<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\ParentLinkCode;
use App\Models\ParentStudentRelation;
use App\Models\School;
use App\Models\User;
use App\Notifications\ParentLinkVerifiedNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminParentRelationController extends Controller
{
    /**
     * Display listing of all parent-student relationships for the school.
     */
    public function index(Request $request): View
    {
        $admin = $request->user();
        $schoolId = $admin->school_id ?? School::current()?->id;

        $query = ParentStudentRelation::with(['parent', 'student.studentProfile', 'verifier'])
            ->latest('id');

        if ($schoolId && ! $admin->isSuperAdmin()) {
            $query->where('school_id', $schoolId);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        if ($request->filled('search')) {
            $search = '%'.$request->query('search').'%';
            $query->where(function ($q) use ($search) {
                $q->whereHas('parent', fn ($sub) => $sub->where('name', 'like', $search)->orWhere('email', 'like', $search))
                    ->orWhereHas('student', fn ($sub) => $sub->where('name', 'like', $search)->orWhere('email', 'like', $search));
            });
        }

        $relations = $query->paginate(20)->withQueryString();

        $activeCodes = ParentLinkCode::with(['student', 'creator'])
            ->where('expires_at', '>', now())
            ->whereColumn('used_count', '<', 'max_uses')
            ->latest('id')
            ->take(10)
            ->get();

        return view('admin.parents.index', compact('relations', 'activeCodes'));
    }

    /**
     * Show form to manually link a parent and student.
     */
    public function create(): View
    {
        $admin = auth()->user();
        $schoolId = $admin->school_id ?? School::current()?->id;

        $parentsQuery = User::where('role', UserRole::PARENT)->where('is_active', true);
        $studentsQuery = User::where('role', UserRole::STUDENT)->where('is_active', true);

        if ($schoolId && ! $admin->isSuperAdmin()) {
            $parentsQuery->where('school_id', $schoolId);
            $studentsQuery->where('school_id', $schoolId);
        }

        $parents = $parentsQuery->orderBy('name')->get();
        $students = $studentsQuery->with('studentProfile')->orderBy('name')->get();

        return view('admin.parents.create', compact('parents', 'students'));
    }

    /**
     * Store manual relationship.
     */
    public function store(Request $request): RedirectResponse
    {
        $admin = $request->user();
        $schoolId = $admin->school_id ?? School::current()?->id ?? 1;

        $validated = $request->validate([
            'parent_id' => ['required', 'exists:users,id'],
            'student_id' => ['required', 'exists:users,id'],
            'relationship_type' => ['required', 'string', 'in:father,mother,guardian,other'],
            'status' => ['required', 'string', 'in:active,pending'],
            'is_primary_contact' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $parent = User::findOrFail($validated['parent_id']);
        $student = User::findOrFail($validated['student_id']);

        if ($parent->role !== UserRole::PARENT) {
            return redirect()->back()->withInput()->withErrors(['parent_id' => 'Pengguna yang dipilih bukan akun Orang Tua.']);
        }

        if ($student->role !== UserRole::STUDENT) {
            return redirect()->back()->withInput()->withErrors(['student_id' => 'Pengguna yang dipilih bukan akun Siswa.']);
        }

        // Prevent cross-school link
        if (! $admin->isSuperAdmin() && ($parent->school_id !== $schoolId || $student->school_id !== $schoolId)) {
            abort(403, 'Akses ditolak: Tidak dapat menghubungkan akun di luar sekolah Anda.');
        }

        $relation = ParentStudentRelation::updateOrCreate(
            [
                'parent_id' => $parent->id,
                'student_id' => $student->id,
            ],
            [
                'school_id' => $schoolId,
                'relationship_type' => $validated['relationship_type'],
                'status' => $validated['status'],
                'is_primary_contact' => $request->boolean('is_primary_contact'),
                'verified_at' => $validated['status'] === 'active' ? now() : null,
                'verified_by' => $validated['status'] === 'active' ? $admin->id : null,
                'notes' => $validated['notes'],
            ]
        );

        if ($relation->isActive()) {
            $parent->notify(new ParentLinkVerifiedNotification($relation));
        }

        return redirect()->route('admin.parents.index')
            ->with('success', 'Relasi orang tua dan siswa berhasil disimpan.');
    }

    /**
     * Verify / approve a pending relationship.
     */
    public function verify(ParentStudentRelation $relation): RedirectResponse
    {
        $admin = auth()->user();

        if (! $admin->isSuperAdmin() && $admin->school_id !== $relation->school_id) {
            abort(403);
        }

        $relation->update([
            'status' => ParentStudentRelation::STATUS_ACTIVE,
            'verified_at' => now(),
            'verified_by' => $admin->id,
        ]);

        $relation->parent->notify(new ParentLinkVerifiedNotification($relation));

        return redirect()->back()
            ->with('success', 'Relasi orang tua dan siswa berhasil diverifikasi dan diaktifkan.');
    }

    /**
     * Reject a relationship.
     */
    public function reject(ParentStudentRelation $relation): RedirectResponse
    {
        $admin = auth()->user();

        if (! $admin->isSuperAdmin() && $admin->school_id !== $relation->school_id) {
            abort(403);
        }

        $relation->update([
            'status' => ParentStudentRelation::STATUS_REJECTED,
        ]);

        return redirect()->back()
            ->with('success', 'Pengajuan relasi berhasil ditolak.');
    }

    /**
     * Generate invitation link code for a student.
     */
    public function generateLinkCode(Request $request): RedirectResponse
    {
        $admin = $request->user();
        $schoolId = $admin->school_id ?? School::current()?->id ?? 1;

        $validated = $request->validate([
            'student_id' => ['required', 'exists:users,id'],
            'relationship_type' => ['nullable', 'string', 'in:father,mother,guardian,other'],
            'valid_days' => ['nullable', 'integer', 'min:1', 'max:90'],
        ]);

        $student = User::findOrFail($validated['student_id']);
        if ($student->role !== UserRole::STUDENT) {
            return redirect()->back()->withErrors(['student_id' => 'Pengguna harus berstatus siswa.']);
        }

        $days = (int) ($validated['valid_days'] ?? 7);
        $relationshipType = $validated['relationship_type'] ?? 'father';

        $code = ParentLinkCode::create([
            'school_id' => $schoolId,
            'student_id' => $student->id,
            'code' => ParentLinkCode::generateCode(),
            'relationship_type' => $relationshipType,
            'expires_at' => now()->addDays($days),
            'max_uses' => 1,
            'used_count' => 0,
            'created_by' => $admin->id,
        ]);

        return redirect()->back()
            ->with('success', "Kode penautan baru berhasil dibuat: {$code->code} (berlaku {$days} hari).");
    }
}
