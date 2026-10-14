<?php

namespace App\Http\Controllers;

use App\Enums\StaffPosition;
use App\Enums\UserRole;
use App\Http\Requests\Staff\ImportStaffRequest;
use App\Models\School;
use App\Models\StaffProfile;
use App\Models\StaffStatusHistory;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StaffImportController extends Controller
{
    /**
     * Show import form and guidelines.
     */
    public function index(): View
    {
        return view('staff.import');
    }

    /**
     * Download CSV template for staff bulk import.
     */
    public function downloadTemplate(): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="eduvia_template_guru_staf.csv"',
        ];

        return response()->stream(function () {
            $handle = fopen('php://output', 'w');

            // UTF-8 BOM for Excel compatibility
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            // Header Row
            fputcsv($handle, [
                'nama_lengkap',
                'email',
                'peran',
                'jabatan',
                'unit_kerja',
                'nip',
                'nuptk',
                'jenis_kelamin',
                'status_kepegawaian',
                'telepon',
            ]);

            // Sample Row 1 (Guru Mapel)
            fputcsv($handle, [
                'Dewi Sartika, S.Pd.',
                'dewi.sartika@sekolah.sch.id',
                'teacher',
                'guru_mapel',
                'Kurikulum',
                '198501152010012001',
                '8765432109876543',
                'female',
                'gty',
                '081298765431',
            ]);

            // Sample Row 2 (Staf Tata Usaha)
            fputcsv($handle, [
                'Agus Setiawan, A.Md.',
                'agus.setiawan@sekolah.sch.id',
                'staff',
                'tata_usaha',
                'Tata Usaha',
                '',
                '',
                'male',
                'pty',
                '081298765432',
            ]);

            fclose($handle);
        }, 200, $headers);
    }

    /**
     * Preview and validate uploaded CSV file.
     */
    public function preview(ImportStaffRequest $request): View|RedirectResponse
    {
        $file = $request->file('file');
        $filePath = $file->getRealPath();

        $rows = [];
        $errors = [];
        $seenEmails = [];
        $headerMap = [];

        if (($handle = fopen($filePath, 'r')) !== false) {
            // Check BOM
            $bom = fread($handle, 3);
            if ($bom !== chr(0xEF).chr(0xBB).chr(0xBF)) {
                rewind($handle);
            }

            $rawHeader = fgetcsv($handle);
            if (! $rawHeader) {
                fclose($handle);

                return back()->withErrors(['file' => 'Berkas CSV kosong atau tidak memiliki header yang valid.']);
            }

            foreach ($rawHeader as $index => $col) {
                $cleanCol = strtolower(trim($col));
                $headerMap[$cleanCol] = $index;
            }

            $requiredCols = ['nama_lengkap', 'email', 'peran', 'jabatan'];
            foreach ($requiredCols as $req) {
                if (! array_key_exists($req, $headerMap)) {
                    fclose($handle);

                    return back()->withErrors([
                        'file' => "Kolom wajib '{$req}' tidak ditemukan dalam berkas CSV. Silakan unduh template resmi.",
                    ]);
                }
            }

            $rowNumber = 1;
            while (($data = fgetcsv($handle)) !== false) {
                $rowNumber++;

                // Skip empty rows
                if (empty(array_filter($data))) {
                    continue;
                }

                $name = trim($data[$headerMap['nama_lengkap']] ?? '');
                $email = strtolower(trim($data[$headerMap['email']] ?? ''));
                $role = strtolower(trim($data[$headerMap['peran']] ?? ''));
                $position = strtolower(trim($data[$headerMap['jabatan']] ?? ''));
                $unit = trim($data[$headerMap['unit_kerja'] ?? null] ?? '');
                $nip = trim($data[$headerMap['nip'] ?? null] ?? '');
                $nuptk = trim($data[$headerMap['nuptk'] ?? null] ?? '');
                $gender = strtolower(trim($data[$headerMap['jenis_kelamin'] ?? null] ?? 'male'));
                $employmentStatus = strtolower(trim($data[$headerMap['status_kepegawaian'] ?? null] ?? 'gty'));
                $phone = trim($data[$headerMap['telepon'] ?? null] ?? '');

                $rowErrors = [];

                if (empty($name)) {
                    $rowErrors[] = 'Nama lengkap wajib diisi.';
                }

                if (empty($email) || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $rowErrors[] = 'Format email tidak valid.';
                } elseif (in_array($email, $seenEmails, true)) {
                    $rowErrors[] = "Email duplikat di dalam berkas (baris {$rowNumber}).";
                } elseif (User::where('email', $email)->exists()) {
                    $rowErrors[] = 'Email sudah terdaftar di sistem.';
                } else {
                    $seenEmails[] = $email;
                }

                $validRoles = [UserRole::TEACHER->value, UserRole::STAFF->value, UserRole::SCHOOL_ADMIN->value];
                if (! in_array($role, $validRoles, true)) {
                    $rowErrors[] = 'Peran tidak valid (harus: teacher, staff, atau school_admin).';
                }

                $validPositions = array_column(StaffPosition::cases(), 'value');
                if (! in_array($position, $validPositions, true)) {
                    $rowErrors[] = 'Jabatan tidak valid.';
                }

                if (in_array($gender, ['l', 'laki-laki', 'pria', 'male'], true)) {
                    $gender = 'male';
                } elseif (in_array($gender, ['p', 'perempuan', 'wanita', 'female'], true)) {
                    $gender = 'female';
                } else {
                    $gender = 'male';
                }

                $rows[] = [
                    'row_number' => $rowNumber,
                    'name' => $name,
                    'email' => $email,
                    'role' => $role,
                    'position' => $position,
                    'work_unit' => $unit,
                    'nip' => $nip,
                    'nuptk' => $nuptk,
                    'gender' => $gender,
                    'employment_status' => $employmentStatus,
                    'phone' => $phone,
                    'is_valid' => empty($rowErrors),
                    'errors' => $rowErrors,
                ];
            }

            fclose($handle);
        }

        if (empty($rows)) {
            return back()->withErrors(['file' => 'Tidak ada data valid yang dapat dibaca dari berkas CSV.']);
        }

        $validCount = count(array_filter($rows, fn ($r) => $r['is_valid']));
        $invalidCount = count($rows) - $validCount;

        return view('staff.import-preview', compact('rows', 'validCount', 'invalidCount'));
    }

    /**
     * Execute the validated batch import.
     */
    public function process(Request $request): RedirectResponse
    {
        $actor = $request->user();
        $school = School::current();
        $schoolId = $school?->id;

        $itemsJson = $request->input('items');
        if (! $itemsJson) {
            return redirect()->route('staff.import.index')
                ->withErrors(['error' => 'Data impor tidak ditemukan. Silakan unggah ulang.']);
        }

        $items = json_decode($itemsJson, true);
        if (! is_array($items) || empty($items)) {
            return redirect()->route('staff.import.index')
                ->withErrors(['error' => 'Format payload data impor tidak valid.']);
        }

        $successCount = 0;

        DB::transaction(function () use ($items, $schoolId, $actor, &$successCount) {
            foreach ($items as $item) {
                // Double-check email uniqueness inside transaction
                if (User::where('email', $item['email'])->exists()) {
                    continue;
                }

                $password = Hash::make(Str::random(32));
                $token = Str::random(64);

                $user = User::create([
                    'school_id' => $schoolId,
                    'name' => $item['name'],
                    'email' => $item['email'],
                    'phone' => $item['phone'] ?? null,
                    'role' => $item['role'],
                    'is_active' => false,
                    'password' => $password,
                ]);

                StaffProfile::create([
                    'user_id' => $user->id,
                    'school_id' => $schoolId,
                    'nip' => $item['nip'] ?? null,
                    'nuptk' => $item['nuptk'] ?? null,
                    'gender' => $item['gender'] ?? 'male',
                    'position' => $item['position'],
                    'work_unit' => $item['work_unit'] ?? null,
                    'employment_status' => $item['employment_status'] ?? 'gty',
                    'phone' => $item['phone'] ?? null,
                    'status' => 'inactive',
                    'invitation_token' => $token,
                    'invitation_sent_at' => now(),
                ]);

                StaffStatusHistory::create([
                    'user_id' => $user->id,
                    'changed_by' => $actor->id,
                    'action' => 'created',
                    'previous_value' => null,
                    'new_value' => $item['role'],
                    'reason' => 'Impor massal data pegawai dari CSV oleh '.$actor->name,
                ]);

                $successCount++;
            }
        });

        return redirect()->route('staff.index')
            ->with('success', "Impor massal selesai! Berhasil menambahkan {$successCount} pegawai baru dengan status menunggu aktivasi.");
    }
}
