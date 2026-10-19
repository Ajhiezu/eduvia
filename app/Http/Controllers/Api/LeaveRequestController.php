<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\SendNotificationJob;
use App\Models\AttendanceRecord;
use App\Models\AttendanceSession;
use App\Models\Classroom;
use App\Models\ClassroomStudent;
use App\Models\ParentStudentRelation;
use App\Models\StudentLeaveRequest;
use App\Models\User;
use App\Services\Notification\NotificationPayload;
use App\Traits\ApiResponse;
use Carbon\CarbonPeriod;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LeaveRequestController extends Controller
{
    use ApiResponse;

    /**
     * Parent: List leave requests filed by the authenticated parent.
     */
    public function parentIndex(Request $request): JsonResponse
    {
        $parent = $request->user();

        $query = StudentLeaveRequest::where('parent_id', $parent->id)
            ->with(['student', 'classroom', 'reviewer:id,name'])
            ->latest();

        if ($request->filled('student_id')) {
            $query->where('student_id', $request->input('student_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $leaves = $query->paginate(15);

        return $this->apiPaginated($leaves, 'Daftar pengajuan izin berhasil dimuat.');
    }

    /**
     * Parent: Submit a new digital leave request (Izin Sakit / Keperluan).
     */
    public function parentStore(Request $request): JsonResponse
    {
        $parent = $request->user();

        $validated = $request->validate([
            'student_id' => ['required', 'integer', 'exists:users,id'],
            'leave_type' => ['required', 'string', 'in:sick,permit,other'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'reason' => ['required', 'string', 'max:1000'],
            'attachment' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ]);

        // Security check: Verify parent has active relationship with student
        $isParentOfStudent = ParentStudentRelation::where('parent_id', $parent->id)
            ->where('student_id', $validated['student_id'])
            ->where('status', 'active')
            ->exists();

        if (! $isParentOfStudent) {
            return $this->apiError('Siswa yang dipilih tidak terhubung secara resmi ke akun Anda.', 403);
        }

        $student = User::findOrFail($validated['student_id']);

        // Determine student's active classroom
        $classroomId = ClassroomStudent::where('student_id', $student->id)
            ->where('enrollment_status', 'active')
            ->value('classroom_id')
            ?? $student->classroom?->id;

        $attachmentPath = null;
        $attachmentName = null;

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $attachmentName = $file->getClientOriginalName();
            $attachmentPath = $file->store('leave_attachments', 'public');
        }

        $leave = StudentLeaveRequest::create([
            'school_id' => $parent->school_id,
            'student_id' => $student->id,
            'parent_id' => $parent->id,
            'classroom_id' => $classroomId,
            'leave_type' => $validated['leave_type'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'reason' => $validated['reason'],
            'attachment_path' => $attachmentPath,
            'attachment_name' => $attachmentName,
            'status' => StudentLeaveRequest::STATUS_PENDING,
        ]);

        // Notify homeroom teacher if assigned
        if ($classroomId) {
            $classroom = Classroom::find($classroomId);
            if ($classroom && $classroom->homeroom_teacher_id) {
                SendNotificationJob::dispatch(new NotificationPayload(
                    userId: $classroom->homeroom_teacher_id,
                    schoolId: $classroom->school_id,
                    eventKey: 'leave_request.created',
                    title: 'Pengajuan Izin Siswa Baru',
                    body: sprintf('Orang tua dari %s mengajukan permohonan izin (%s). Silakan periksa di aplikasi.', $student->name, $leave->typeLabel()),
                    data: ['leave_id' => $leave->id, 'student_id' => $student->id],
                    isUrgent: false
                ));
            }
        }

        return $this->apiSuccess($leave->load(['student', 'classroom']), 'Pengajuan izin berhasil dikirimkan ke wali kelas.', 201);
    }

    /**
     * Teacher: List pending/reviewed leave requests for the teacher's homeroom or classes.
     */
    public function teacherIndex(Request $request): JsonResponse
    {
        $teacher = $request->user();

        // Get classrooms where this user is homeroom teacher
        $homeroomClassroomIds = Classroom::where('homeroom_teacher_id', $teacher->id)
            ->pluck('id')
            ->toArray();

        $query = StudentLeaveRequest::whereIn('classroom_id', $homeroomClassroomIds)
            ->with(['student', 'parent:id,name,phone', 'classroom'])
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $leaves = $query->paginate(20);

        return $this->apiPaginated($leaves, 'Daftar pengajuan izin kelas berhasil dimuat.');
    }

    /**
     * Teacher: Review (Approve/Reject) a student leave request.
     * Automatically updates daily attendance records when approved.
     */
    public function teacherReview(Request $request, StudentLeaveRequest $leave): JsonResponse
    {
        $teacher = $request->user();

        // Validate authority: Teacher must be homeroom teacher of the class
        $classroom = Classroom::find($leave->classroom_id);
        if (! $classroom || $classroom->homeroom_teacher_id !== $teacher->id) {
            return $this->apiError('Anda tidak memiliki wewenang untuk meninjau izin kelas ini.', 403);
        }

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:approved,rejected'],
            'rejection_reason' => ['nullable', 'required_if:status,rejected', 'string', 'max:500'],
        ]);

        DB::transaction(function () use ($leave, $validated, $teacher, $classroom) {
            $leave->update([
                'status' => $validated['status'],
                'reviewed_by' => $teacher->id,
                'reviewed_at' => now(),
                'rejection_reason' => $validated['rejection_reason'] ?? null,
            ]);

            // When approved: Automatically update daily attendance records across the date range
            if ($validated['status'] === StudentLeaveRequest::STATUS_APPROVED) {
                $statusToRecord = $leave->leave_type === StudentLeaveRequest::TYPE_SICK ? 'sick' : 'permit';
                $period = CarbonPeriod::create($leave->start_date, $leave->end_date);

                foreach ($period as $date) {
                    $dateStr = $date->format('Y-m-d');

                    // Find or create daily homeroom attendance session
                    $session = AttendanceSession::firstOrCreate(
                        [
                            'school_id' => $leave->school_id,
                            'classroom_id' => $leave->classroom_id,
                            'date' => $dateStr,
                            'session_type' => 'daily',
                        ],
                        [
                            'academic_year_id' => $classroom->academic_year_id,
                            'teacher_id' => $teacher->id,
                            'start_time' => '07:00:00',
                            'end_time' => '15:00:00',
                            'topic' => 'Presensi Harian',
                            'status' => 'draft',
                        ]
                    );

                    AttendanceRecord::updateOrCreate(
                        [
                            'attendance_session_id' => $session->id,
                            'student_id' => $leave->student_id,
                        ],
                        [
                            'status' => $statusToRecord,
                            'notes' => sprintf('Izin disetujui: %s', mb_substr($leave->reason, 0, 100)),
                        ]
                    );
                }
            }
        });

        // Notify parent about review decision
        $student = $leave->student;
        $statusText = $validated['status'] === StudentLeaveRequest::STATUS_APPROVED ? 'disetujui' : 'ditolak';

        SendNotificationJob::dispatch(new NotificationPayload(
            userId: $leave->parent_id,
            schoolId: $leave->school_id,
            eventKey: 'leave_request.reviewed',
            title: 'Hasil Peninjauan Izin Siswa',
            body: sprintf('Pengajuan izin untuk %s telah %s oleh wali kelas.', $student?->name ?? 'Ananda', $statusText),
            data: [
                'leave_id' => $leave->id,
                'status' => $validated['status'],
                'rejection_reason' => $validated['rejection_reason'] ?? null,
            ],
            isUrgent: false
        ));

        return $this->apiSuccess($leave->fresh(['student', 'reviewer:id,name']), 'Hasil peninjauan permohonan izin berhasil disimpan.');
    }
}
