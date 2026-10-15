<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use App\Models\School;
use App\Models\StudentReportCard;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class ReportCardPdfController extends Controller
{
    protected function currentSchoolId(): ?int
    {
        return auth()->user()?->school_id ?? School::current()?->id;
    }

    /**
     * Authorize download access.
     */
    protected function authorizePdfAccess(StudentReportCard $reportCard): void
    {
        $user = auth()->user();
        $schoolId = $this->currentSchoolId();

        if ($schoolId && (int) $reportCard->school_id !== (int) $schoolId) {
            abort(403, 'Akses ditolak: Dokumen ini tidak terdaftar pada sekolah Anda.');
        }

        // Student can only access their own published report card
        if ($user->isStudent()) {
            if ((int) $reportCard->student_id !== (int) $user->id) {
                abort(403, 'Akses ditolak: Anda hanya berhak mengunduh rapor milik Anda sendiri.');
            }

            if (! $reportCard->isPublished()) {
                abort(403, 'Akses ditolak: Rapor ini belum resmi diterbitkan.');
            }

            return;
        }

        // Homeroom teacher can access report cards of their homeroom class
        if ($user->isTeacher()) {
            $isHomeroom = (int) $reportCard->homeroom_teacher_id === (int) $user->id ||
                Classroom::where('id', $reportCard->classroom_id)->where('homeroom_teacher_id', $user->id)->exists();

            if (! $isHomeroom && ! $user->isSchoolAdmin() && ! $user->isSuperAdmin()) {
                abort(403, 'Akses ditolak: Anda bukan wali kelas dari siswa ini.');
            }

            return;
        }

        // Parent can only access published report cards of their verified active children
        if ($user->isParent()) {
            $isLinkedChild = $user->activeChildren()->where('users.id', $reportCard->student_id)->exists();
            if (! $isLinkedChild) {
                abort(403, 'Akses ditolak: Siswa ini bukan anak yang terhubung aktif dengan akun Anda.');
            }

            if (! $reportCard->isPublished()) {
                abort(403, 'Akses ditolak: Rapor ini belum resmi diterbitkan.');
            }

            return;
        }

        // Admin can access
        if ($user->isSchoolAdmin() || $user->isSuperAdmin()) {
            return;
        }

        abort(403, 'Akses ditolak.');
    }

    /**
     * Generate printable PDF stream for a student report card.
     */
    public function download(StudentReportCard $reportCard, Request $request): Response
    {
        $this->authorizePdfAccess($reportCard);

        $reportCard->loadMissing([
            'school',
            'student.studentProfile',
            'classroom.gradeLevel',
            'academicYear',
            'semester',
            'homeroomTeacher.staffProfile',
            'items.subject',
        ]);

        $snapshot = $reportCard->isPublished() && ! empty($reportCard->snapshot)
            ? $reportCard->snapshot
            : $reportCard->buildSnapshot();

        $pdf = Pdf::loadView('reports.pdf.report-card', [
            'reportCard' => $reportCard,
            'data' => $snapshot,
        ])->setPaper('a4', 'portrait');

        $studentName = Str::slug($snapshot['student']['name'] ?? 'siswa');
        $nis = Str::slug($snapshot['student']['nis'] ?? 'nis');
        $semesterName = Str::slug($snapshot['academic']['semester_name'] ?? 'semester');
        $academicYear = Str::slug($snapshot['academic']['academic_year_name'] ?? 'ta');

        $filename = "Rapor_{$academicYear}_{$semesterName}_{$nis}_{$studentName}.pdf";

        if ($request->query('mode') === 'download') {
            return $pdf->download($filename);
        }

        return $pdf->stream($filename);
    }
}
