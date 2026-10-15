<?php

namespace App\Http\Controllers;

use App\Http\Requests\Subject\StoreSubjectRequest;
use App\Http\Requests\Subject\UpdateSubjectRequest;
use App\Models\School;
use App\Models\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubjectController extends Controller
{
    /**
     * Display a listing of subjects with filters and metrics.
     */
    public function index(Request $request): View
    {
        $school = School::current();
        $schoolId = $school?->id ?? $request->user()?->school_id;

        $search = $request->input('search');
        $group = $request->input('group');
        $level = $request->input('educational_level');
        $curriculum = $request->input('curriculum');
        $status = $request->input('status');

        $query = Subject::query()
            ->when($schoolId, fn ($q) => $q->where('school_id', $schoolId))
            ->when($search, function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('academic_program', 'like', "%{$search}%");
                });
            })
            ->when($group, fn ($q) => $q->where('group', $group))
            ->when($level, fn ($q) => $q->where('educational_level', $level))
            ->when($curriculum, fn ($q) => $q->where('curriculum', $curriculum))
            ->when($status !== null && $status !== '', fn ($q) => $q->where('is_active', (bool) $status))
            ->withCount('teachingAssignments')
            ->orderBy('order')
            ->orderBy('name');

        $subjects = $query->paginate(15)->withQueryString();

        // Metrics calculations
        $baseQuery = Subject::query()->when($schoolId, fn ($q) => $q->where('school_id', $schoolId));
        $totalCount = (clone $baseQuery)->count();
        $activeCount = (clone $baseQuery)->where('is_active', true)->count();
        $umumCount = (clone $baseQuery)->where('group', 'Umum')->count();
        $peminatanKejuruanCount = (clone $baseQuery)->whereIn('group', ['Peminatan', 'Kejuruan'])->count();

        return view('subjects.index', compact(
            'subjects',
            'search',
            'group',
            'level',
            'curriculum',
            'status',
            'totalCount',
            'activeCount',
            'umumCount',
            'peminatanKejuruanCount'
        ));
    }

    /**
     * Show the form for creating a new subject.
     */
    public function create(): View
    {
        $school = School::current();

        return view('subjects.create', compact('school'));
    }

    /**
     * Store a newly created subject in storage.
     */
    public function store(StoreSubjectRequest $request): RedirectResponse
    {
        $school = School::current();
        $schoolId = $school?->id ?? $request->user()?->school_id;

        $data = $request->validated();
        $data['school_id'] = $schoolId;
        $data['is_active'] = $request->boolean('is_active', true);
        $data['order'] = $data['order'] ?? 0;

        $subject = Subject::create($data);

        return redirect()->route('subjects.index')
            ->with('status', "Mata pelajaran '{$subject->name}' berhasil ditambahkan ke kurikulum.");
    }

    /**
     * Show the form for editing the specified subject.
     */
    public function edit(Subject $subject): View
    {
        $this->authorizeSubject($subject);

        return view('subjects.edit', compact('subject'));
    }

    /**
     * Update the specified subject in storage.
     */
    public function update(UpdateSubjectRequest $request, Subject $subject): RedirectResponse
    {
        $this->authorizeSubject($subject);

        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active', true);

        $subject->update($data);

        return redirect()->route('subjects.index')
            ->with('status', "Mata pelajaran '{$subject->name}' berhasil diperbarui.");
    }

    /**
     * Remove the specified subject from storage with safety checks.
     */
    public function destroy(Subject $subject): RedirectResponse
    {
        $this->authorizeSubject($subject);

        if ($subject->teachingAssignments()->exists()) {
            return redirect()->back()->withErrors([
                'error' => "Mata pelajaran '{$subject->name}' tidak dapat dihapus karena sudah memiliki riwayat penugasan mengajar/jadwal. Non-aktifkan mata pelajaran ini untuk menghentikan penggunaannya tanpa merusak data historis.",
            ]);
        }

        $subject->delete();

        return redirect()->route('subjects.index')
            ->with('status', "Mata pelajaran '{$subject->name}' berhasil dihapus.");
    }

    /**
     * Ensure the subject belongs to the current school scope.
     */
    protected function authorizeSubject(Subject $subject): void
    {
        $user = request()->user();
        if ($user && $user->isSuperAdmin()) {
            return;
        }

        $school = School::current();
        $schoolId = $school?->id ?? $user?->school_id;

        if ($subject->school_id && $subject->school_id !== $schoolId) {
            abort(403, 'Akses ke mata pelajaran sekolah lain tidak diizinkan.');
        }
    }
}
