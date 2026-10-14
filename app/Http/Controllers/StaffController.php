<?php

namespace App\Http\Controllers;

use App\Enums\StaffPosition;
use App\Enums\UserRole;
use App\Http\Requests\Staff\StoreStaffRequest;
use App\Http\Requests\Staff\UpdateStaffRequest;
use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\School;
use App\Models\Semester;
use App\Models\StaffProfile;
use App\Models\StaffStatusHistory;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class StaffController extends Controller
{
    /**
     * Display a listing of staff and teachers.
     */
    public function index(Request $request): View
    {
        $actor = $request->user();
        $school = School::current();
        $schoolId = $school?->id;

        $search = $request->input('search');
        $role = $request->input('role');
        $position = $request->input('position');
        $status = $request->input('status');

        $query = User::query()
            ->whereIn('role', [
                UserRole::TEACHER->value,
                UserRole::STAFF->value,
                UserRole::SCHOOL_ADMIN->value,
                UserRole::SUPER_ADMIN->value,
            ])
            ->with(['staffProfile', 'school', 'teachingAssignments.subject', 'teachingAssignments.classroom'])
            ->latest('id');

        // Tenant Isolation: School Admin only sees their school
        if (! $actor->isSuperAdmin()) {
            $query->where(function ($q) use ($schoolId) {
                $q->where('school_id', $schoolId)
                    ->orWhereHas('staffProfile', fn ($sp) => $sp->where('school_id', $schoolId));
            });
            // Don't show super admins to school admins
            $query->where('role', '!=', UserRole::SUPER_ADMIN->value);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhereHas('staffProfile', function ($sp) use ($search) {
                        $sp->where('nip', 'like', "%{$search}%")
                            ->orWhere('nuptk', 'like', "%{$search}%")
                            ->orWhere('work_unit', 'like', "%{$search}%");
                    });
            });
        }

        if ($role) {
            $query->where('role', $role);
        }

        if ($position) {
            $query->whereHas('staffProfile', fn ($sp) => $sp->where('position', $position));
        }

        if ($status) {
            if ($status === 'active') {
                $query->where('is_active', true);
            } elseif ($status === 'inactive') {
                $query->where('is_active', false);
            } elseif ($status === 'pending_invitation') {
                $query->whereHas('staffProfile', fn ($sp) => $sp->whereNotNull('invitation_token')->whereNull('activated_at'));
            }
        }

        $staffMembers = $query->paginate(15)->withQueryString();

        // Calculate Overview Metrics
        $baseMetricsQuery = User::whereIn('role', [
            UserRole::TEACHER->value,
            UserRole::STAFF->value,
            UserRole::SCHOOL_ADMIN->value,
        ]);

        if (! $actor->isSuperAdmin()) {
            $baseMetricsQuery->where(function ($q) use ($schoolId) {
                $q->where('school_id', $schoolId)
                    ->orWhereHas('staffProfile', fn ($sp) => $sp->where('school_id', $schoolId));
            });
        }

        $totalStaffCount = (clone $baseMetricsQuery)->count();
        $teacherCount = (clone $baseMetricsQuery)->where('role', UserRole::TEACHER->value)->count();
        $educationStaffCount = (clone $baseMetricsQuery)->where('role', UserRole::STAFF->value)->count();
        $activeCount = (clone $baseMetricsQuery)->where('is_active', true)->count();
        $pendingInvitationCount = (clone $baseMetricsQuery)->whereHas('staffProfile', function ($sp) {
            $sp->whereNotNull('invitation_token')->whereNull('activated_at');
        })->count();

        $positions = StaffPosition::cases();

        return view('staff.index', compact(
            'staffMembers',
            'search',
            'role',
            'position',
            'status',
            'positions',
            'totalStaffCount',
            'teacherCount',
            'educationStaffCount',
            'activeCount',
            'pendingInvitationCount'
        ));
    }

    /**
     * Show the form for creating a new staff/teacher account.
     */
    public function create(): View
    {
        $actor = auth()->user();
        $school = School::current();

        $positions = StaffPosition::cases();
        $allowedRoles = [
            UserRole::TEACHER,
            UserRole::STAFF,
            UserRole::SCHOOL_ADMIN,
        ];

        return view('staff.create', compact('positions', 'allowedRoles', 'school'));
    }

    /**
     * Store a newly created staff/teacher in database.
     */
    public function store(StoreStaffRequest $request): RedirectResponse
    {
        $actor = $request->user();
        $school = School::current();
        $schoolId = $school?->id;

        $validated = $request->validated();
        $creationMode = $validated['creation_mode'];

        $invitationToken = null;
        $invitationSentAt = null;
        $emailVerifiedAt = null;

        if ($creationMode === 'invitation') {
            $password = Hash::make(Str::random(32));
            $invitationToken = Str::random(64);
            $invitationSentAt = now();
            $isActive = false;
        } else {
            $password = Hash::make($validated['password']);
            $isActive = true;
            $emailVerifiedAt = now();
        }

        $user = DB::transaction(function () use ($validated, $schoolId, $password, $isActive, $emailVerifiedAt, $invitationToken, $invitationSentAt, $actor) {
            $newUser = User::create([
                'school_id' => $schoolId,
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'role' => $validated['role'],
                'is_active' => $isActive,
                'password' => $password,
                'email_verified_at' => $emailVerifiedAt,
            ]);

            StaffProfile::create([
                'user_id' => $newUser->id,
                'school_id' => $schoolId,
                'nip' => $validated['nip'] ?? null,
                'nuptk' => $validated['nuptk'] ?? null,
                'nik' => $validated['nik'] ?? null,
                'gender' => $validated['gender'],
                'birth_place' => $validated['birth_place'] ?? null,
                'birth_date' => $validated['birth_date'] ?? null,
                'position' => $validated['position'],
                'work_unit' => $validated['work_unit'] ?? null,
                'employment_status' => $validated['employment_status'],
                'join_date' => $validated['join_date'] ?? null,
                'last_education' => $validated['last_education'] ?? null,
                'address' => $validated['address'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'status' => $isActive ? 'active' : 'inactive',
                'invitation_token' => $invitationToken,
                'invitation_sent_at' => $invitationSentAt,
                'notes' => $validated['notes'] ?? null,
            ]);

            StaffStatusHistory::create([
                'user_id' => $newUser->id,
                'changed_by' => $actor->id,
                'action' => 'created',
                'previous_value' => null,
                'new_value' => $validated['role'],
                'reason' => 'Pembuatan akun pegawai baru oleh '.$actor->name,
            ]);

            return $newUser;
        });

        if ($creationMode === 'invitation' && $invitationToken) {
            $activationUrl = route('staff.activate', ['token' => $invitationToken]);
            session()->flash('flash_activation_url', $activationUrl);

            return redirect()->route('staff.show', $user)
                ->with('success', "Akun untuk {$user->name} berhasil dibuat. Silakan salin tautan aktivasi berikut untuk diberikan kepada pegawai.");
        }

        return redirect()->route('staff.show', $user)
            ->with('success', "Akun guru/staf {$user->name} berhasil dibuat dan langsung aktif.");
    }

    /**
     * Display the specified staff profile and teaching assignments.
     */
    public function show(User $staff): View
    {
        $this->authorizeStaffAccess($staff);

        $staff->load([
            'staffProfile',
            'school',
            'teachingAssignments.classroom',
            'teachingAssignments.subject',
            'teachingAssignments.academicYear',
            'teachingAssignments.semester',
            'homeroomClassrooms',
            'staffStatusHistories.actor',
        ]);

        $school = School::current();
        $schoolId = $school?->id;

        // Dependency counters
        $dependencyCount = [
            'teachingAssignments' => $staff->teachingAssignments()->count(),
            'homeroomClassrooms' => $staff->homeroomClassrooms()->count(),
            'learningMaterials' => $staff->learningMaterials()->count(),
            'assignments' => $staff->assignments()->count(),
        ];

        // Academic assignment form data
        $classrooms = Classroom::where('school_id', $schoolId)->where('is_active', true)->orderBy('name')->get();
        $subjects = Subject::where('school_id', $schoolId)->where('is_active', true)->orderBy('name')->get();
        $academicYears = AcademicYear::where('school_id', $schoolId)->orderByDesc('start_date')->get();
        $semesters = Semester::whereHas('academicYear', fn ($q) => $q->where('school_id', $schoolId))->orderBy('id')->get();

        return view('staff.show', compact(
            'staff',
            'dependencyCount',
            'classrooms',
            'subjects',
            'academicYears',
            'semesters'
        ));
    }

    /**
     * Show the form for editing the staff member.
     */
    public function edit(User $staff): View
    {
        $this->authorizeStaffAccess($staff);

        $actor = auth()->user();
        if ($staff->isSuperAdmin() && ! $actor->isSuperAdmin()) {
            abort(403, 'Anda tidak berwenang mengedit akun Super Administrator.');
        }

        $staff->load(['staffProfile', 'school']);

        $positions = StaffPosition::cases();
        $allowedRoles = [
            UserRole::TEACHER,
            UserRole::STAFF,
            UserRole::SCHOOL_ADMIN,
        ];

        if ($actor->isSuperAdmin()) {
            $allowedRoles[] = UserRole::SUPER_ADMIN;
        }

        return view('staff.edit', compact('staff', 'positions', 'allowedRoles'));
    }

    /**
     * Update the specified staff member in database.
     */
    public function update(UpdateStaffRequest $request, User $staff): RedirectResponse
    {
        $this->authorizeStaffAccess($staff);

        $actor = $request->user();
        $validated = $request->validated();

        DB::transaction(function () use ($staff, $validated, $actor) {
            $previousRole = $staff->role->value;
            $previousStatus = $staff->is_active ? 'active' : 'inactive';

            $userUpdate = [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'role' => $validated['role'],
                'is_active' => $validated['status'] === 'active',
            ];

            if (! empty($validated['password'])) {
                $userUpdate['password'] = Hash::make($validated['password']);
            }

            $staff->update($userUpdate);

            $profileData = [
                'nip' => $validated['nip'] ?? null,
                'nuptk' => $validated['nuptk'] ?? null,
                'nik' => $validated['nik'] ?? null,
                'gender' => $validated['gender'],
                'birth_place' => $validated['birth_place'] ?? null,
                'birth_date' => $validated['birth_date'] ?? null,
                'position' => $validated['position'],
                'work_unit' => $validated['work_unit'] ?? null,
                'employment_status' => $validated['employment_status'],
                'join_date' => $validated['join_date'] ?? null,
                'last_education' => $validated['last_education'] ?? null,
                'address' => $validated['address'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'status' => $validated['status'],
                'notes' => $validated['notes'] ?? null,
            ];

            $staff->staffProfile()->updateOrCreate(['user_id' => $staff->id], $profileData);

            if ($previousRole !== $validated['role']) {
                StaffStatusHistory::create([
                    'user_id' => $staff->id,
                    'changed_by' => $actor->id,
                    'action' => 'role_changed',
                    'previous_value' => $previousRole,
                    'new_value' => $validated['role'],
                    'reason' => 'Perubahan peran hak akses oleh '.$actor->name,
                ]);
            }

            if ($previousStatus !== $validated['status']) {
                StaffStatusHistory::create([
                    'user_id' => $staff->id,
                    'changed_by' => $actor->id,
                    'action' => 'status_changed',
                    'previous_value' => $previousStatus,
                    'new_value' => $validated['status'],
                    'reason' => 'Perubahan status keaktifan oleh '.$actor->name,
                ]);
            }
        });

        return redirect()->route('staff.show', $staff)
            ->with('success', "Data pegawai {$staff->name} berhasil diperbarui.");
    }

    /**
     * Toggle active/inactive status of a staff member.
     */
    public function toggleStatus(Request $request, User $staff): RedirectResponse
    {
        $this->authorizeStaffAccess($staff);

        $actor = $request->user();
        if ($staff->id === $actor->id) {
            return back()->withErrors(['error' => 'Anda tidak dapat menonaktifkan akun Anda sendiri.']);
        }

        $newActive = ! $staff->is_active;
        $statusStr = $newActive ? 'active' : 'inactive';
        $reason = $request->input('reason', $newActive ? 'Pengaktifan kembali akun' : 'Penonaktifan akun oleh administrator');

        DB::transaction(function () use ($staff, $newActive, $statusStr, $reason, $actor) {
            $previous = $staff->is_active ? 'active' : 'inactive';

            $staff->update(['is_active' => $newActive]);

            if ($staff->staffProfile) {
                $staff->staffProfile->update(['status' => $statusStr]);
            }

            StaffStatusHistory::create([
                'user_id' => $staff->id,
                'changed_by' => $actor->id,
                'action' => $newActive ? 'activated' : 'deactivated',
                'previous_value' => $previous,
                'new_value' => $statusStr,
                'reason' => $reason,
            ]);
        });

        $message = $newActive
            ? "Akun {$staff->name} berhasil diaktifkan kembali."
            : "Akun {$staff->name} berhasil dinonaktifkan. Seluruh histori akademik dan tugas tetap terjaga aman.";

        return back()->with('success', $message);
    }

    /**
     * Resend/generate new invitation token for an unactivated staff member.
     */
    public function resendInvitation(User $staff): RedirectResponse
    {
        $this->authorizeStaffAccess($staff);

        $newToken = Str::random(64);

        $staff->staffProfile()->updateOrCreate(
            ['user_id' => $staff->id],
            [
                'invitation_token' => $newToken,
                'invitation_sent_at' => now(),
                'status' => 'inactive',
            ]
        );

        StaffStatusHistory::create([
            'user_id' => $staff->id,
            'changed_by' => auth()->id(),
            'action' => 'invitation_sent',
            'previous_value' => null,
            'new_value' => 'Undangan Baru',
            'reason' => 'Pembuatan ulang token undangan aktivasi',
        ]);

        $activationUrl = route('staff.activate', ['token' => $newToken]);
        session()->flash('flash_activation_url', $activationUrl);

        return back()->with('success', "Token aktivasi baru berhasil dibuat untuk {$staff->name}. Silakan salin tautan aktivasi.");
    }

    /**
     * Safely delete a staff account if no dependent data exists.
     */
    public function destroy(User $staff): RedirectResponse
    {
        $this->authorizeStaffAccess($staff);

        $actor = auth()->user();
        if ($staff->id === $actor->id) {
            return back()->withErrors(['error' => 'Anda tidak dapat menghapus akun Anda sendiri.']);
        }

        // Check dependencies
        $hasAssignments = $staff->teachingAssignments()->exists();
        $hasMaterials = $staff->learningMaterials()->exists();
        $hasAssignmentTasks = $staff->assignments()->exists();
        $hasHomeroom = $staff->homeroomClassrooms()->exists();

        if ($hasAssignments || $hasMaterials || $hasAssignmentTasks || $hasHomeroom) {
            return back()->withErrors([
                'error' => "Pegawai {$staff->name} memiliki data pembelajaran aktif (materi, tugas, atau kelas). Demi integritas data akademik, akun tidak boleh dihapus. Gunakan fitur 'Nonaktifkan Akun' sebagai gantinya.",
            ]);
        }

        DB::transaction(function () use ($staff) {
            $staff->staffProfile()?->delete();
            $staff->delete();
        });

        return redirect()->route('staff.index')
            ->with('success', "Akun pegawai {$staff->name} berhasil dihapus.");
    }

    /**
     * Guard tenant isolation: Ensure actor can only access staff in their school.
     */
    protected function authorizeStaffAccess(User $staff): void
    {
        $actor = auth()->user();

        if ($actor->isSuperAdmin()) {
            return;
        }

        $school = School::current();
        $schoolId = $school?->id;

        $staffSchoolId = $staff->school_id ?? $staff->staffProfile?->school_id;

        if ($staffSchoolId && $staffSchoolId !== $schoolId) {
            abort(403, 'Akses ditolak: Pegawai ini berada di sekolah yang berbeda.');
        }
    }
}
