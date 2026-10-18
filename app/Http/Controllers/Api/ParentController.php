<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\Invoice;
use App\Models\ParentStudentRelation;
use App\Models\SchoolAnnouncement;
use App\Models\User;
use App\Traits\ApiResponse;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ParentController extends Controller
{
    use ApiResponse;

    /**
     * Get list of children linked to this parent from real DB relationships.
     */
    public function children(Request $request): JsonResponse
    {
        $parent = $request->user();

        $relations = ParentStudentRelation::where('parent_id', $parent->id)
            ->where('status', 'active')
            ->with(['student.classroom', 'student.studentProfile'])
            ->get();

        $children = $relations->map(function ($rel) {
            $st = $rel->student;
            $classroom = $st?->classroom;
            $profile = $st?->studentProfile;

            return [
                'id' => $st->id,
                'name' => $st->name,
                'email' => $st->email,
                'nis' => $profile?->nis ?? '-',
                'nisn' => $profile?->nisn ?? '0081234567',
                'class_name' => $classroom?->name ?? 'VII-A',
                'relationship' => $rel->relationship_type ?? 'Orang Tua / Wali',
                'avatar' => $st->avatar ? asset('storage/'.$st->avatar) : null,
            ];
        });

        return $this->apiSuccess(
            data: $children,
            message: 'Daftar anak berhasil dimuat.'
        );
    }

    /**
     * Resolve target student belonging to this parent.
     */
    protected function resolveStudent(Request $request): ?User
    {
        $parentId = $request->user()->id;
        $studentId = $request->input('student_id');

        $query = ParentStudentRelation::where('parent_id', $parentId)->where('status', 'active');

        if ($studentId) {
            $query->where('student_id', $studentId);
        }

        $relation = $query->first() ?? ParentStudentRelation::where('parent_id', $parentId)->where('status', 'active')->first();

        return $relation?->student;
    }

    /**
     * Parent dashboard overview for the selected child.
     */
    public function dashboard(Request $request): JsonResponse
    {
        $student = $this->resolveStudent($request);

        if (! $student) {
            return $this->apiError('Tidak ada data anak yang terhubung ke akun Anda.', 404);
        }

        $classroom = $student->classroom;
        $profile = $student->studentProfile;

        // 1. Attendance statistics
        $attendanceRecords = AttendanceRecord::where('student_id', $student->id)->get();
        $totalSessions = $attendanceRecords->count();
        $presentCount = $attendanceRecords->where('status', 'present')->count();
        $sickCount = $attendanceRecords->where('status', 'sick')->count();
        $permitCount = $attendanceRecords->where('status', 'permit')->count();
        $absentCount = $attendanceRecords->where('status', 'absent')->count();
        $attendancePercent = $totalSessions > 0 ? round(($presentCount / $totalSessions) * 100, 1) : 100.0;

        // 2. Pending invoices (SPP)
        $pendingInvoice = Invoice::where('student_id', $student->id)
            ->whereIn('status', ['issued', 'overdue'])
            ->orderBy('due_date')
            ->first();

        // 3. Announcements for parents
        $announcements = SchoolAnnouncement::where('status', 'published')
            ->whereIn('target_audience', ['all', 'parents'])
            ->orderByDesc('is_pinned')
            ->orderByDesc('published_at')
            ->take(3)
            ->get()
            ->map(fn ($a) => [
                'id' => $a->id,
                'title' => $a->title,
                'category' => $a->category,
                'content' => $a->content,
                'date' => $a->published_at ? $a->published_at->translatedFormat('d M Y') : $a->created_at->translatedFormat('d M Y'),
                'is_pinned' => (bool) $a->is_pinned,
            ]);

        return $this->apiSuccess(
            data: [
                'child' => [
                    'id' => $student->id,
                    'name' => $student->name,
                    'nisn' => $profile?->nisn ?? '0081234567',
                    'class_name' => $classroom?->name ?? 'VII-A',
                ],
                'attendance_summary' => [
                    'percentage' => $attendancePercent,
                    'present' => $presentCount,
                    'sick' => $sickCount,
                    'permit' => $permitCount,
                    'absent' => $absentCount,
                ],
                'pending_invoice' => $pendingInvoice ? [
                    'id' => $pendingInvoice->id,
                    'title' => $pendingInvoice->title,
                    'amount' => (int) $pendingInvoice->total_amount,
                    'due_date' => Carbon::parse($pendingInvoice->due_date)->translatedFormat('d M Y'),
                    'status' => $pendingInvoice->status,
                ] : null,
                'announcements' => $announcements,
            ],
            message: 'Dashboard wali berhasil dimuat.'
        );
    }

    /**
     * Detailed attendance history for selected child.
     */
    public function attendance(Request $request): JsonResponse
    {
        $student = $this->resolveStudent($request);

        if (! $student) {
            return $this->apiError('Tidak ada data anak.', 404);
        }

        $records = AttendanceRecord::where('student_id', $student->id)
            ->with(['session'])
            ->get()
            ->sortByDesc(fn ($r) => $r->session?->date ?? $r->created_at)
            ->values();

        $history = $records->map(function ($r) {
            $date = $r->session?->date ? Carbon::parse($r->session->date) : $r->created_at;
            $statusLabel = match ($r->status) {
                'present' => 'Hadir Tepat Waktu',
                'sick' => 'Sakit (Surat Dokter)',
                'permit' => 'Izin',
                'absent' => 'Alpha',
                default => 'Hadir'
            };

            return [
                'id' => $r->id,
                'date' => $date->translatedFormat('l, d M Y'),
                'status' => $r->status,
                'status_label' => $statusLabel,
                'time' => $r->session?->start_time ? substr($r->session->start_time, 0, 5).' WIB' : '07:00 WIB',
                'notes' => $r->notes ?? '-',
            ];
        });

        $total = $records->count();
        $present = $records->where('status', 'present')->count();
        $sick = $records->where('status', 'sick')->count();
        $permit = $records->where('status', 'permit')->count();
        $absent = $records->where('status', 'absent')->count();

        return $this->apiSuccess(
            data: [
                'stats' => [
                    'present' => $present,
                    'sick' => $sick,
                    'permit' => $permit,
                    'absent' => $absent,
                ],
                'history' => $history,
            ],
            message: 'Data kehadiran berhasil dimuat.'
        );
    }

    /**
     * School invoices / SPP bills for selected child.
     */
    public function finance(Request $request): JsonResponse
    {
        $student = $this->resolveStudent($request);

        if (! $student) {
            return $this->apiError('Tidak ada data anak.', 404);
        }

        $invoices = Invoice::where('student_id', $student->id)
            ->orderByDesc('due_date')
            ->with(['items'])
            ->get()
            ->map(fn ($inv) => [
                'id' => $inv->id,
                'invoice_number' => $inv->invoice_number,
                'title' => $inv->title,
                'total_amount' => (int) $inv->total_amount,
                'paid_amount' => (int) $inv->paid_amount,
                'due_date' => Carbon::parse($inv->due_date)->translatedFormat('d M Y'),
                'status' => $inv->status, // 'issued', 'paid', 'overdue'
                'notes' => $inv->notes,
            ]);

        return $this->apiSuccess(
            data: $invoices,
            message: 'Data tagihan berhasil dimuat.'
        );
    }
}
