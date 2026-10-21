<?php

namespace App\Services\Pilot;

use App\Enums\UserRole;
use App\Models\AuditLog;
use App\Models\Classroom;
use App\Models\ClassroomStudent;
use App\Models\Pilot\PilotImportBatch;
use App\Models\School;
use App\Models\StaffProfile;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;

class PilotOnboardingImportService
{
    /**
     * Parse and validate CSV data before import, returning detailed row-by-row preview.
     *
     * @return array{
     *     total_rows: int,
     *     valid_rows_count: int,
     *     error_rows_count: int,
     *     duplicate_rows_count: int,
     *     valid_rows: array<int, array<string, mixed>>,
     *     errors: array<int, array{row: int, reason: string}>,
     *     can_proceed: bool
     * }
     */
    public function previewCsv(UploadedFile|string $file, string $entityType, School $school): array
    {
        $rows = $this->readCsvRows($file);
        $totalRows = count($rows);

        if ($totalRows === 0) {
            throw new InvalidArgumentException('Berkas CSV kosong atau tidak memiliki baris data.');
        }

        $validRows = [];
        $errors = [];
        $seenIdentifiers = [];

        // Preload classrooms for quick reference check
        $classrooms = Classroom::where('school_id', $school->id)->pluck('id', 'name')->toArray();

        foreach ($rows as $index => $row) {
            $rowNum = $index + 2; // Row 1 is header

            if ($entityType === 'students') {
                $err = $this->validateStudentRow($row, $rowNum, $school, $seenIdentifiers, $classrooms);
            } elseif ($entityType === 'teachers') {
                $err = $this->validateTeacherRow($row, $rowNum, $school, $seenIdentifiers);
            } else {
                throw new InvalidArgumentException("Tipe entitas onboarding '{$entityType}' tidak didukung.");
            }

            if (! empty($err)) {
                $errors[] = ['row' => $rowNum, 'reason' => implode('; ', $err)];
            } else {
                $validRows[] = $row;
            }
        }

        $duplicateCount = count(array_filter($errors, fn ($e) => str_contains($e['reason'], 'Duplikat')));

        return [
            'total_rows' => $totalRows,
            'valid_rows_count' => count($validRows),
            'error_rows_count' => count($errors),
            'duplicate_rows_count' => $duplicateCount,
            'valid_rows' => $validRows,
            'errors' => $errors,
            'can_proceed' => count($errors) === 0,
        ];
    }

    /**
     * Execute atomic batch import with surgical rollback tracking.
     *
     * @param  array<int, array<string, mixed>>  $validatedRows
     * @return array{
     *     batch_code: string,
     *     total_imported: int,
     *     reconciliation_report: array<string, mixed>
     * }
     */
    public function executeImport(
        array $validatedRows,
        string $entityType,
        string $fileName,
        School $school,
        User $operator
    ): array {
        if (empty($validatedRows)) {
            throw new InvalidArgumentException('Tidak ada data valid yang dapat diimpor.');
        }

        $batchCode = 'BATCH-'.date('YmdHis').'-'.strtoupper(Str::random(4));
        $createdUserIds = [];

        $classrooms = Classroom::where('school_id', $school->id)->get()->keyBy('name');

        DB::transaction(function () use ($validatedRows, $entityType, $school, &$createdUserIds, $classrooms) {
            foreach ($validatedRows as $row) {
                $tempPassword = Str::password(12);

                $user = User::create([
                    'school_id' => $school->id,
                    'name' => trim($row['name']),
                    'email' => trim($row['email']),
                    'phone' => ! empty($row['phone']) ? trim($row['phone']) : null,
                    'role' => $entityType === 'students' ? UserRole::STUDENT->value : UserRole::TEACHER->value,
                    'password' => Hash::make($tempPassword),
                    'is_active' => true,
                ]);

                $createdUserIds[] = $user->id;

                if ($entityType === 'students') {
                    StudentProfile::create([
                        'school_id' => $school->id,
                        'user_id' => $user->id,
                        'nis' => $row['nis'] ?? null,
                        'nisn' => $row['nisn'] ?? null,
                        'gender' => strtolower($row['gender'] ?? 'L') === 'p' ? 'female' : 'male',
                        'birth_place' => $row['birth_place'] ?? null,
                        'birth_date' => ! empty($row['birth_date']) ? date('Y-m-d', strtotime($row['birth_date'])) : null,
                    ]);

                    if (! empty($row['classroom']) && isset($classrooms[$row['classroom']])) {
                        $cls = $classrooms[$row['classroom']];
                        ClassroomStudent::create([
                            'classroom_id' => $cls->id,
                            'student_id' => $user->id,
                            'academic_year_id' => $cls->academic_year_id,
                            'enrollment_status' => 'active',
                            'joined_at' => now()->toDateString(),
                        ]);
                    }
                } elseif ($entityType === 'teachers') {
                    StaffProfile::create([
                        'school_id' => $school->id,
                        'user_id' => $user->id,
                        'employee_number' => $row['nip'] ?? null,
                        'gender' => strtolower($row['gender'] ?? 'L') === 'p' ? 'female' : 'male',
                    ]);
                }
            }
        });

        $reconciliation = [
            'entity_type' => $entityType,
            'source_rows' => count($validatedRows),
            'imported_records' => count($createdUserIds),
            'discrepancy' => 0,
            'generated_at' => now()->toIso8601String(),
            'operator_name' => $operator->name,
            'school_name' => $school->name,
        ];

        $batch = PilotImportBatch::create([
            'school_id' => $school->id,
            'batch_code' => $batchCode,
            'entity_type' => $entityType,
            'file_name' => $fileName,
            'total_rows' => count($validatedRows),
            'imported_rows' => count($createdUserIds),
            'failed_rows' => 0,
            'reconciliation_report' => $reconciliation,
            'created_ids' => $createdUserIds,
            'status' => 'completed',
            'imported_by' => $operator->id,
        ]);

        AuditLog::record(
            action: 'pilot_import_executed',
            auditable: $batch,
            newValues: $reconciliation,
            description: "Impor data pilot massal ({$entityType}) berhasil dieksekusi: {$batchCode}.",
            schoolId: $school->id
        );

        return [
            'batch_code' => $batchCode,
            'total_imported' => count($createdUserIds),
            'reconciliation_report' => $reconciliation,
        ];
    }

    /**
     * Surgical Rollback of a previously imported batch without touching pre-existing data.
     */
    public function rollbackBatch(string $batchCode, School $school, User $operator): bool
    {
        $batch = PilotImportBatch::where('batch_code', $batchCode)
            ->where('school_id', $school->id)
            ->firstOrFail();

        if ($batch->isRolledBack()) {
            throw new RuntimeException("Batch impor '{$batchCode}' telah dibatalkan sebelumnya.");
        }

        $userIds = $batch->created_ids ?? [];

        DB::transaction(function () use ($batch, $userIds, $school, $operator) {
            if (! empty($userIds)) {
                // Delete child profiles first
                StudentProfile::whereIn('user_id', $userIds)->delete();
                StaffProfile::whereIn('user_id', $userIds)->delete();
                ClassroomStudent::whereIn('student_id', $userIds)->delete();

                // Delete users
                User::whereIn('id', $userIds)->where('school_id', $school->id)->delete();
            }

            $batch->update([
                'status' => 'rolled_back',
                'rolled_back_at' => now(),
                'rolled_back_by' => $operator->id,
            ]);

            AuditLog::record(
                action: 'pilot_import_rolled_back',
                auditable: $batch,
                newValues: ['status' => 'rolled_back', 'deleted_records_count' => count($userIds)],
                description: "Pembatalan (Rollback) batch data pilot {$batch->batch_code} berhasil dijalankan.",
                schoolId: $school->id
            );
        });

        return true;
    }

    /**
     * Validate individual student CSV row.
     *
     * @param  array<string, string>  $row
     * @param  array<string, bool>  $seen
     * @param  array<string, int>  $classrooms
     * @return array<int, string>
     */
    protected function validateStudentRow(array $row, int $rowNum, School $school, array &$seen, array $classrooms): array
    {
        $errors = [];

        $name = trim($row['name'] ?? '');
        $email = trim($row['email'] ?? '');
        $nis = trim($row['nis'] ?? '');
        $nisn = trim($row['nisn'] ?? '');
        $classroom = trim($row['classroom'] ?? '');

        if (empty($name)) {
            $errors[] = 'Nama siswa wajib diisi';
        }

        if (empty($email) || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Format email tidak valid';
        } else {
            if (isset($seen['email'][$email])) {
                $errors[] = "Duplikat email di dalam berkas ({$email})";
            }
            $seen['email'][$email] = true;

            if (User::where('email', $email)->exists()) {
                $errors[] = "Email ({$email}) sudah terdaftar di sistem";
            }
        }

        if (! empty($nisn)) {
            if (! preg_match('/^\d{10}$/', $nisn)) {
                $errors[] = "NISN harus terdiri dari tepat 10 digit angka ({$nisn})";
            } else {
                if (isset($seen['nisn'][$nisn])) {
                    $errors[] = "Duplikat NISN di dalam berkas ({$nisn})";
                }
                $seen['nisn'][$nisn] = true;

                if (StudentProfile::where('nisn', $nisn)->where('school_id', $school->id)->exists()) {
                    $errors[] = "NISN ({$nisn}) sudah ada pada sekolah ini";
                }
            }
        }

        if (! empty($classroom) && ! isset($classrooms[$classroom])) {
            $errors[] = "Kelas '{$classroom}' tidak ditemukan di sekolah ini";
        }

        return $errors;
    }

    /**
     * Validate individual teacher CSV row.
     *
     * @param  array<string, string>  $row
     * @param  array<string, bool>  $seen
     * @return array<int, string>
     */
    protected function validateTeacherRow(array $row, int $rowNum, School $school, array &$seen): array
    {
        $errors = [];

        $name = trim($row['name'] ?? '');
        $email = trim($row['email'] ?? '');
        $nip = trim($row['nip'] ?? '');

        if (empty($name)) {
            $errors[] = 'Nama guru wajib diisi';
        }

        if (empty($email) || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Format email guru tidak valid';
        } else {
            if (isset($seen['email'][$email])) {
                $errors[] = "Duplikat email di dalam berkas ({$email})";
            }
            $seen['email'][$email] = true;

            if (User::where('email', $email)->exists()) {
                $errors[] = "Email guru ({$email}) sudah terdaftar di sistem";
            }
        }

        return $errors;
    }

    /**
     * Helper to read CSV rows as associative array.
     *
     * @return array<int, array<string, string>>
     */
    protected function readCsvRows(UploadedFile|string $file): array
    {
        $path = $file instanceof UploadedFile ? $file->getRealPath() : $file;
        $handle = fopen($path, 'r');
        if (! $handle) {
            throw new RuntimeException('Tidak dapat membuka berkas CSV.');
        }

        $header = fgetcsv($handle);
        if (! $header) {
            fclose($handle);

            return [];
        }

        // Clean UTF-8 BOM from header keys
        $cleanHeader = array_map(function ($col) {
            return trim(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', strtolower($col)));
        }, $header);

        $rows = [];
        while (($data = fgetcsv($handle)) !== false) {
            if (count($data) === count($cleanHeader)) {
                $row = array_combine($cleanHeader, $data);
                // Filter out empty rows
                if (! empty(array_filter($row))) {
                    $rows[] = $row;
                }
            }
        }

        fclose($handle);

        return $rows;
    }
}
