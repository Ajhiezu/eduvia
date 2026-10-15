<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\Classroom;
use App\Models\School;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StudentImportExportController extends Controller
{
    /**
     * Show import form and guidelines.
     */
    public function index(): View
    {
        $school = School::current();
        $schoolId = $school?->id;

        $classrooms = Classroom::when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->active()
            ->with(['academicYear', 'gradeLevel'])
            ->orderBy('name')
            ->get();

        return view('students.import', compact('classrooms'));
    }

    /**
     * Download CSV template for student bulk import.
     */
    public function downloadTemplate(): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="eduvia_template_siswa.csv"',
        ];

        return response()->stream(function () {
            $handle = fopen('php://output', 'w');

            // UTF-8 BOM for Excel compatibility
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            // Header Row
            fputcsv($handle, [
                'nama_lengkap',
                'email',
                'nis',
                'nisn',
                'jenis_kelamin',
                'tempat_lahir',
                'tanggal_lahir',
                'nama_orang_tua',
                'telepon_orang_tua',
                'telepon',
            ]);

            // Sample Row 1
            fputcsv($handle, [
                'Ahmad Fauzi Rahman',
                'ahmad.fauzi@siswa.sch.id',
                '20261001',
                '0081234567',
                'male',
                'Jakarta',
                '2009-05-14',
                'Bambang Rahman',
                '081234567890',
                '081234567891',
            ]);

            // Sample Row 2
            fputcsv($handle, [
                'Siti Nurhaliza',
                'siti.nurhaliza@siswa.sch.id',
                '20261002',
                '0081234568',
                'female',
                'Bandung',
                '2009-08-22',
                'Farhan Hidayat',
                '081398765432',
                '081398765433',
            ]);

            fclose($handle);
        }, 200, $headers);
    }

    /**
     * Preview and validate uploaded CSV file.
     */
    public function preview(Request $request): View|RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:5120'],
            'classroom_id' => ['nullable', 'exists:classrooms,id'],
        ], [
            'file.required' => 'Pilih file CSV peserta didik terlebih dahulu.',
            'file.mimes' => 'Format file harus berupa CSV (.csv).',
            'file.max' => 'Ukuran file maksimal 5 MB.',
        ]);

        $school = School::current();
        $schoolId = $school?->id ?? $request->user()?->school_id;
        $classroomId = $request->input('classroom_id');
        $selectedClassroom = $classroomId ? Classroom::find($classroomId) : null;

        $file = $request->file('file');
        $filePath = $file->getRealPath();

        $rows = [];
        $errors = [];
        $existingEmails = User::pluck('email')->toArray();
        $existingNis = StudentProfile::when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->whereNotNull('nis')
            ->pluck('nis')
            ->toArray();

        $seenEmails = [];
        $seenNis = [];

        if (($handle = fopen($filePath, 'r')) !== false) {
            // Check & strip UTF-8 BOM if present
            $bom = fread($handle, 3);
            if ($bom !== "\xEF\xBB\xBF") {
                rewind($handle);
            }

            $header = fgetcsv($handle);
            if (! $header) {
                fclose($handle);

                return back()->withErrors(['file' => 'File CSV kosong atau tidak memiliki baris header.']);
            }

            $line = 1;
            while (($data = fgetcsv($handle)) !== false) {
                $line++;
                // Skip empty lines
                if (empty(array_filter($data))) {
                    continue;
                }

                $name = trim($data[0] ?? '');
                $email = strtolower(trim($data[1] ?? ''));
                $nis = trim($data[2] ?? '');
                $nisn = trim($data[3] ?? '');
                $gender = strtolower(trim($data[4] ?? 'male'));
                $birthPlace = trim($data[5] ?? '');
                $birthDate = trim($data[6] ?? '');
                $parentName = trim($data[7] ?? '');
                $parentPhone = trim($data[8] ?? '');
                $phone = trim($data[9] ?? '');

                $rowErrors = [];

                if (empty($name)) {
                    $rowErrors[] = 'Nama lengkap wajib diisi.';
                }

                if (empty($email) || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $rowErrors[] = 'Format email tidak valid.';
                } elseif (in_array($email, $existingEmails) || in_array($email, $seenEmails)) {
                    $rowErrors[] = 'Email sudah terdaftar.';
                } else {
                    $seenEmails[] = $email;
                }

                if (! empty($nis)) {
                    if (in_array($nis, $existingNis) || in_array($nis, $seenNis)) {
                        $rowErrors[] = 'NIS sudah terdaftar.';
                    } else {
                        $seenNis[] = $nis;
                    }
                }

                if (! in_array($gender, ['male', 'female', 'l', 'p', 'laki-laki', 'perempuan'])) {
                    $gender = 'male';
                } else {
                    $gender = in_array($gender, ['female', 'p', 'perempuan']) ? 'female' : 'male';
                }

                $rowItem = [
                    'line' => $line,
                    'name' => $name,
                    'email' => $email,
                    'nis' => $nis,
                    'nisn' => $nisn,
                    'gender' => $gender,
                    'birth_place' => $birthPlace,
                    'birth_date' => $birthDate,
                    'parent_name' => $parentName,
                    'parent_phone' => $parentPhone,
                    'phone' => $phone,
                    'errors' => $rowErrors,
                ];

                $rows[] = $rowItem;
                if (! empty($rowErrors)) {
                    $errors[] = "Baris {$line}: ".implode(', ', $rowErrors);
                }
            }
            fclose($handle);
        }

        $validCount = count(array_filter($rows, fn ($r) => empty($r['errors'])));
        $invalidCount = count($rows) - $validCount;

        return view('students.import-preview', compact(
            'rows',
            'validCount',
            'invalidCount',
            'errors',
            'classroomId',
            'selectedClassroom'
        ));
    }

    /**
     * Process validated import data inside database transaction.
     */
    public function process(Request $request): RedirectResponse
    {
        $request->validate([
            'students' => ['required', 'string'],
            'classroom_id' => ['nullable', 'exists:classrooms,id'],
        ]);

        $school = School::current();
        $schoolId = $school?->id ?? $request->user()?->school_id;
        $classroomId = $request->input('classroom_id');
        $classroom = $classroomId ? Classroom::find($classroomId) : null;

        $studentsData = json_decode($request->input('students'), true);
        if (! is_array($studentsData) || empty($studentsData)) {
            return redirect()->route('students.import.index')
                ->withErrors(['error' => 'Data impor tidak ditemukan atau sudah kadaluarsa. Silakan unggah ulang.']);
        }

        $importedCount = 0;

        DB::transaction(function () use ($studentsData, $schoolId, $classroom, &$importedCount) {
            foreach ($studentsData as $item) {
                // Skip rows that had errors
                if (! empty($item['errors'])) {
                    continue;
                }

                $user = User::create([
                    'name' => $item['name'],
                    'email' => $item['email'],
                    'phone' => $item['phone'] ?: null,
                    'role' => UserRole::STUDENT->value,
                    'school_id' => $schoolId,
                    'is_active' => true,
                    'password' => Hash::make('Password123!'),
                ]);

                StudentProfile::create([
                    'user_id' => $user->id,
                    'school_id' => $schoolId,
                    'nis' => $item['nis'] ?: null,
                    'nisn' => $item['nisn'] ?: null,
                    'gender' => $item['gender'],
                    'birth_place' => $item['birth_place'] ?: null,
                    'birth_date' => $item['birth_date'] ?: null,
                    'parent_name' => $item['parent_name'] ?: null,
                    'parent_phone' => $item['parent_phone'] ?: null,
                    'student_status' => 'active',
                    'enrollment_date' => now()->toDateString(),
                    'notes' => 'Diimpor melalui bulk CSV import',
                ]);

                if ($classroom) {
                    $classroom->addStudent(
                        $user->id,
                        now()->toDateString(),
                        'Penempatan otomatis melalui impor data siswa massal'
                    );
                }

                $importedCount++;
            }
        });

        return redirect()->route('students.index')
            ->with('status', "Berhasil mengimpor {$importedCount} peserta didik ke dalam database.");
    }

    /**
     * Export student roster to CSV with applied filters.
     */
    public function exportCsv(Request $request): StreamedResponse
    {
        $school = School::current();
        $schoolId = $school?->id ?? $request->user()?->school_id;

        $search = $request->input('search');
        $status = $request->input('status');
        $classroomId = $request->input('classroom_id');
        $gradeLevelId = $request->input('grade_level_id');

        $query = User::query()
            ->where('role', UserRole::STUDENT->value)
            ->when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->with(['studentProfile', 'classroomEnrollments.classroom.gradeLevel'])
            ->when($search, function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhereHas('studentProfile', function ($p) use ($search) {
                            $p->where('nis', 'like', "%{$search}%")
                                ->orWhere('nisn', 'like', "%{$search}%");
                        });
                });
            })
            ->when($status, function ($q) use ($status) {
                $q->whereHas('studentProfile', fn ($p) => $p->where('student_status', $status));
            })
            ->when($classroomId, function ($q) use ($classroomId) {
                $q->whereHas('classroomEnrollments', function ($ce) use ($classroomId) {
                    $ce->where('classroom_id', $classroomId)
                        ->where('enrollment_status', 'active');
                });
            })
            ->when($gradeLevelId, function ($q) use ($gradeLevelId) {
                $q->whereHas('classroomEnrollments.classroom', function ($c) use ($gradeLevelId) {
                    $c->where('grade_level_id', $gradeLevelId);
                });
            })
            ->latest('id');

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="data_peserta_didik_'.now()->format('Ymd_His').'.csv"',
        ];

        return response()->stream(function () use ($query) {
            $handle = fopen('php://output', 'w');

            // UTF-8 BOM
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            // Header columns
            fputcsv($handle, [
                'ID Siswa',
                'Nama Lengkap',
                'Email',
                'NIS',
                'NISN',
                'Jenis Kelamin',
                'Tempat Lahir',
                'Tanggal Lahir',
                'Status Kesiswaan',
                'Kelas / Rombel Aktif',
                'Nama Orang Tua / Wali',
                'Telepon Orang Tua',
                'Telepon Siswa',
            ]);

            $query->chunk(100, function ($students) use ($handle) {
                foreach ($students as $student) {
                    $profile = $student->studentProfile;
                    $currentEnrollment = $student->classroomEnrollments
                        ->where('enrollment_status', 'active')
                        ->first();

                    fputcsv($handle, [
                        $student->id,
                        $student->name,
                        $student->email,
                        $profile?->nis ?? '-',
                        $profile?->nisn ?? '-',
                        $profile?->gender === 'female' ? 'Perempuan' : 'Laki-laki',
                        $profile?->birth_place ?? '-',
                        $profile?->birth_date?->format('Y-m-d') ?? '-',
                        $profile?->student_status ?? 'active',
                        $currentEnrollment?->classroom?->name ?? 'Belum ada rombel',
                        $profile?->parent_name ?? '-',
                        $profile?->parent_phone ?? '-',
                        $student->phone ?? '-',
                    ]);
                }
            });

            fclose($handle);
        }, 200, $headers);
    }
}
