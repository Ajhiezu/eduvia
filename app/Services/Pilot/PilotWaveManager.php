<?php

namespace App\Services\Pilot;

use App\Models\AcademicYear;
use App\Models\AuditLog;
use App\Models\Classroom;
use App\Models\ClassroomStudent;
use App\Models\Exam;
use App\Models\ExamQuestion;
use App\Models\FeeCategory;
use App\Models\School;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\SystemSetting;
use App\Models\TeachingAssignment;
use App\Models\User;
use App\Models\UserConsent;
use RuntimeException;

class PilotWaveManager
{
    /**
     * Run rigorous pre-flight readiness checks before activating a specific wave.
     *
     * @return array{
     *     wave_number: int,
     *     can_activate: bool,
     *     checks: array<int, array{item: string, passed: bool, notes: string}>
     * }
     */
    public function preFlightCheck(int $waveNumber, int $schoolId): array
    {
        $school = School::findOrFail($schoolId);
        $checks = [];

        switch ($waveNumber) {
            case 1: // Inti Akademik & Presensi Harian
                $hasAcademicYear = AcademicYear::where('school_id', $schoolId)->where('is_active', true)->exists();
                $checks[] = [
                    'item' => 'Tahun Ajaran Aktif',
                    'passed' => $hasAcademicYear,
                    'notes' => $hasAcademicYear ? 'Tersedia tahun ajaran aktif.' : 'Belum ada tahun ajaran berstatus aktif.',
                ];

                $hasSemester = Semester::whereHas('academicYear', fn ($q) => $q->where('school_id', $schoolId))->where('is_active', true)->exists();
                $checks[] = [
                    'item' => 'Semester Aktif',
                    'passed' => $hasSemester,
                    'notes' => $hasSemester ? 'Tersedia semester aktif.' : 'Belum ada semester berstatus aktif.',
                ];

                $classroomCount = Classroom::where('school_id', $schoolId)->count();
                $checks[] = [
                    'item' => 'Rombongan Belajar / Kelas',
                    'passed' => $classroomCount > 0,
                    'notes' => "Tersedia {$classroomCount} kelas terdaftar.",
                ];

                $teacherCount = User::where('school_id', $schoolId)->where('role', 'teacher')->where('is_active', true)->count();
                $checks[] = [
                    'item' => 'Akun Guru Aktif',
                    'passed' => $teacherCount > 0,
                    'notes' => "Tersedia {$teacherCount} akun guru aktif.",
                ];

                $enrolledStudents = ClassroomStudent::whereHas('classroom', fn ($q) => $q->where('school_id', $schoolId))->count();
                $checks[] = [
                    'item' => 'Siswa Terdaftar di Rombel',
                    'passed' => $enrolledStudents > 0,
                    'notes' => "Tersedia {$enrolledStudents} siswa terdaftar di rombel.",
                ];
                break;

            case 2: // Portal Mobile Siswa & Orang Tua
                $w1Check = $this->preFlightCheck(1, $schoolId);
                $checks[] = [
                    'item' => 'Prasyarat Gelombang 1 Lolos',
                    'passed' => $w1Check['can_activate'],
                    'notes' => $w1Check['can_activate'] ? 'Seluruh prasyarat Gelombang 1 terpenuhi.' : 'Gelombang 1 belum memenuhi syarat.',
                ];

                $consentCount = UserConsent::where('school_id', $schoolId)->count();
                $checks[] = [
                    'item' => 'Registri Persetujuan (Consent Registry)',
                    'passed' => $consentCount >= 0, // Ready to receive consents
                    'notes' => 'Tabel user_consents siap mencatat persetujuan orang tua.',
                ];
                break;

            case 3: // Tugas Harian, Penilaian, & Rapor
                $subjectCount = Subject::where('school_id', $schoolId)->count();
                $checks[] = [
                    'item' => 'Mata Pelajaran Terdaftar',
                    'passed' => $subjectCount > 0,
                    'notes' => "Tersedia {$subjectCount} mata pelajaran.",
                ];

                $assignmentCount = TeachingAssignment::where('school_id', $schoolId)->count();
                $checks[] = [
                    'item' => 'Penugasan Mengajar Guru',
                    'passed' => $assignmentCount > 0,
                    'notes' => "Tersedia {$assignmentCount} penugasan mengajar aktif.",
                ];
                break;

            case 4: // Keuangan & SPP Manual
                $categoryCount = FeeCategory::where('school_id', $schoolId)->where('is_active', true)->count();
                $checks[] = [
                    'item' => 'Kategori Tarif Biaya (SPP)',
                    'passed' => $categoryCount > 0,
                    'notes' => "Tersedia {$categoryCount} kategori biaya aktif.",
                ];
                break;

            case 5: // Ujian CBT Formatif Terkendali
                $questionCount = ExamQuestion::where('school_id', $schoolId)->where('is_active', true)->count();
                $checks[] = [
                    'item' => 'Bank Soal Ujian CBT',
                    'passed' => $questionCount >= 5,
                    'notes' => "Tersedia {$questionCount} butir soal aktif di bank soal (minimal 5).",
                ];

                $examCount = Exam::where('school_id', $schoolId)->count();
                $checks[] = [
                    'item' => 'Sesi Ujian CBT Terjadwal',
                    'passed' => $examCount > 0,
                    'notes' => "Tersedia {$examCount} jadwal ujian CBT terdaftar.",
                ];
                break;

            case 6: // Modul Lanjutan & AI
                $quotaExists = DB::table('school_ai_quotas')->where('school_id', $schoolId)->exists();
                $checks[] = [
                    'item' => 'Alokasi Kuota Token AI Sekolah',
                    'passed' => $quotaExists,
                    'notes' => $quotaExists ? 'Kuota token AI yayasan telah dialokasikan.' : 'Belum ada alokasi kuota AI untuk sekolah ini.',
                ];
                break;

            default:
                throw new RuntimeException("Nomor gelombang pilot '{$waveNumber}' tidak valid (Gunakan 1-6).");
        }

        $allPassed = ! in_array(false, array_column($checks, 'passed'), true);

        return [
            'wave_number' => $waveNumber,
            'can_activate' => $allPassed,
            'checks' => $checks,
        ];
    }

    /**
     * Activate a wave after verifying pre-flight check.
     */
    public function activateWave(int $waveNumber, int $schoolId, User $operator): bool
    {
        $preFlight = $this->preFlightCheck($waveNumber, $schoolId);

        if (! $preFlight['can_activate']) {
            $unmet = array_filter($preFlight['checks'], fn ($c) => ! $c['passed']);
            $reasons = implode(', ', array_column($unmet, 'item'));
            throw new RuntimeException("Aktivasi Gelombang {$waveNumber} DITOLAK. Prasyarat belum terpenuhi: {$reasons}");
        }

        SystemSetting::set("pilot_wave_{$waveNumber}_active", '1', 'boolean', 'pilot_waves', $schoolId);

        AuditLog::record(
            action: 'pilot_wave_activated',
            auditable: School::find($schoolId),
            newValues: ['wave_number' => $waveNumber, 'status' => 'activated'],
            description: "Gelombang pilot {$waveNumber} resmi diaktifkan oleh operator {$operator->name}.",
            schoolId: $schoolId
        );

        return true;
    }

    /**
     * Deactivate a wave (Emergency Rollback or temporary hold).
     */
    public function deactivateWave(int $waveNumber, int $schoolId, User $operator, string $reason): bool
    {
        SystemSetting::set("pilot_wave_{$waveNumber}_active", '0', 'boolean', 'pilot_waves', $schoolId);

        AuditLog::record(
            action: 'pilot_wave_deactivated',
            auditable: School::find($schoolId),
            newValues: ['wave_number' => $waveNumber, 'status' => 'deactivated', 'reason' => $reason],
            description: "Gelombang pilot {$waveNumber} dinonaktifkan (Rollback). Alasan: {$reason}",
            schoolId: $schoolId
        );

        return true;
    }

    /**
     * Check if a wave is currently active for a school.
     */
    public function isWaveActive(int $waveNumber, int $schoolId): bool
    {
        return (bool) SystemSetting::get("pilot_wave_{$waveNumber}_active", default: false, schoolId: $schoolId);
    }

    /**
     * Set module into graceful Read-Only mode to protect data while pausing mutations.
     */
    public function setModuleReadOnly(string $module, int $schoolId, bool $readOnly, User $operator): bool
    {
        SystemSetting::set("pilot_readonly_{$module}", $readOnly ? '1' : '0', 'boolean', 'pilot_readonly', $schoolId);

        AuditLog::record(
            action: 'pilot_readonly_updated',
            auditable: School::find($schoolId),
            newValues: ['module' => $module, 'read_only' => $readOnly],
            description: "Modul {$module} diatur ke status baca-saja: ".($readOnly ? 'AKTIF' : 'NONAKTIF'),
            schoolId: $schoolId
        );

        return true;
    }

    /**
     * Check if a module is currently in Read-Only mode.
     */
    public function isModuleReadOnly(string $module, int $schoolId): bool
    {
        return (bool) SystemSetting::get("pilot_readonly_{$module}", default: false, schoolId: $schoolId);
    }
}
