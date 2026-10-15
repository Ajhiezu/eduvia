<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\Classroom;
use App\Models\GradeLevel;
use App\Models\School;
use App\Models\SchoolAnnouncement;
use App\Models\User;
use App\Notifications\NewSchoolAnnouncementNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SchoolAnnouncementController extends Controller
{
    /**
     * Display a listing of announcements.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $schoolId = $user->school_id ?? School::current()?->id;

        $query = SchoolAnnouncement::with(['author', 'gradeLevel', 'classroom'])
            ->latest('published_at')
            ->latest('id');

        if ($schoolId && ! $user->isSuperAdmin()) {
            $query->where('school_id', $schoolId);
        }

        // Role-based visibility
        if ($user->hasRole(UserRole::SUPER_ADMIN, UserRole::SCHOOL_ADMIN, UserRole::TEACHER)) {
            if ($request->filled('status')) {
                $query->where('status', $request->query('status'));
            }
        } elseif ($user->hasRole(UserRole::PARENT)) {
            $query->published()
                ->whereIn('target_audience', [SchoolAnnouncement::AUDIENCE_ALL, SchoolAnnouncement::AUDIENCE_PARENTS]);
        } elseif ($user->hasRole(UserRole::STUDENT)) {
            $query->published()
                ->whereIn('target_audience', [SchoolAnnouncement::AUDIENCE_ALL, SchoolAnnouncement::AUDIENCE_STUDENTS]);
        } else {
            $query->published();
        }

        if ($request->filled('category')) {
            $query->where('category', $request->query('category'));
        }

        if ($request->filled('search')) {
            $search = '%'.$request->query('search').'%';
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', $search)
                    ->orWhere('content', 'like', $search);
            });
        }

        $announcements = $query->paginate(12)->withQueryString();

        return view('announcements.index', compact('announcements'));
    }

    /**
     * Show the form for creating a new announcement.
     */
    public function create(): View
    {
        $user = auth()->user();
        if (! $user->hasRole(UserRole::SUPER_ADMIN, UserRole::SCHOOL_ADMIN, UserRole::TEACHER)) {
            abort(403, 'Akses ditolak: Hanya guru dan administrator yang dapat membuat pengumuman.');
        }

        $schoolId = $user->school_id ?? School::current()?->id;
        $school = $schoolId ? School::find($schoolId) : School::current();

        $gradeLevels = GradeLevel::query()
            ->active()
            ->when($school && ! empty($school->educational_levels), function ($q) use ($school) {
                $q->whereIn('educational_level', (array) $school->educational_levels);
            })
            ->orderBy('order')
            ->get();

        $classrooms = Classroom::when($schoolId, fn ($q) => $q->where('school_id', $schoolId))->get();

        return view('announcements.create', compact('gradeLevels', 'classrooms'));
    }

    /**
     * Store a newly created announcement.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        if (! $user->hasRole(UserRole::SUPER_ADMIN, UserRole::SCHOOL_ADMIN, UserRole::TEACHER)) {
            abort(403);
        }

        $schoolId = $user->school_id ?? School::current()?->id ?? 1;

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string', 'min:10'],
            'category' => ['required', 'string', 'in:general,academic,event,holiday,urgent'],
            'target_audience' => ['required', 'string', 'in:all,parents,students,teachers'],
            'grade_level_id' => ['nullable', 'exists:grade_levels,id'],
            'classroom_id' => ['nullable', 'exists:classrooms,id'],
            'status' => ['required', 'string', 'in:draft,published,archived'],
            'is_pinned' => ['nullable', 'boolean'],
            'attachment' => ['nullable', 'file', 'max:5120', 'mimes:pdf,doc,docx,jpg,jpeg,png'],
        ], [
            'title.required' => 'Judul pengumuman wajib diisi.',
            'content.required' => 'Isi pengumuman wajib diisi.',
            'attachment.max' => 'Ukuran file lampiran maksimal 5 MB.',
        ]);

        $attachmentPath = null;
        $attachmentName = null;

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $attachmentName = $file->getClientOriginalName();
            $attachmentPath = $file->store('announcements/attachments', 'public');
        }

        $announcement = SchoolAnnouncement::create([
            'school_id' => $schoolId,
            'created_by' => $user->id,
            'title' => $validated['title'],
            'content' => $validated['content'],
            'category' => $validated['category'],
            'target_audience' => $validated['target_audience'],
            'grade_level_id' => $validated['grade_level_id'] ?? null,
            'classroom_id' => $validated['classroom_id'] ?? null,
            'status' => $validated['status'],
            'is_pinned' => $request->boolean('is_pinned'),
            'published_at' => $validated['status'] === 'published' ? now() : null,
            'attachment_path' => $attachmentPath,
            'attachment_name' => $attachmentName,
        ]);

        // If published, notify target users
        if ($announcement->status === SchoolAnnouncement::STATUS_PUBLISHED) {
            $recipientQuery = User::where('school_id', $schoolId)->where('is_active', true);

            if ($announcement->target_audience === SchoolAnnouncement::AUDIENCE_PARENTS) {
                $recipientQuery->where('role', UserRole::PARENT);
            } elseif ($announcement->target_audience === SchoolAnnouncement::AUDIENCE_STUDENTS) {
                $recipientQuery->where('role', UserRole::STUDENT);
            } elseif ($announcement->target_audience === SchoolAnnouncement::AUDIENCE_TEACHERS) {
                $recipientQuery->where('role', UserRole::TEACHER);
            }

            // Chunk and notify to prevent memory overload
            $recipientQuery->chunk(100, function ($users) use ($announcement) {
                foreach ($users as $recipient) {
                    $recipient->notify(new NewSchoolAnnouncementNotification($announcement));
                }
            });
        }

        return redirect()->route('announcements.index')
            ->with('success', 'Pengumuman sekolah berhasil dibuat.');
    }

    /**
     * Display the specified announcement.
     */
    public function show(SchoolAnnouncement $announcement): View
    {
        $user = auth()->user();

        // Tenant isolation
        if (! $user->isSuperAdmin() && $user->school_id && $announcement->school_id !== $user->school_id) {
            abort(403, 'Akses ditolak: Pengumuman ini milik sekolah lain.');
        }

        // Draft authorization
        if ($announcement->status === SchoolAnnouncement::STATUS_DRAFT) {
            if (! $user->hasRole(UserRole::SUPER_ADMIN, UserRole::SCHOOL_ADMIN) && $announcement->created_by !== $user->id) {
                abort(404, 'Pengumuman ini masih berstatus draf.');
            }
        }

        // Audience authorization
        if ($user->hasRole(UserRole::PARENT) && ! in_array($announcement->target_audience, [SchoolAnnouncement::AUDIENCE_ALL, SchoolAnnouncement::AUDIENCE_PARENTS], true)) {
            abort(403, 'Akses ditolak: Pengumuman ini tidak ditujukan untuk orang tua.');
        }

        if ($user->hasRole(UserRole::STUDENT) && ! in_array($announcement->target_audience, [SchoolAnnouncement::AUDIENCE_ALL, SchoolAnnouncement::AUDIENCE_STUDENTS], true)) {
            abort(403, 'Akses ditolak: Pengumuman ini tidak ditujukan untuk siswa.');
        }

        $announcement->increment('view_count');
        $announcement->load(['author', 'gradeLevel', 'classroom']);

        return view('announcements.show', compact('announcement'));
    }

    /**
     * Show edit form.
     */
    public function edit(SchoolAnnouncement $announcement): View
    {
        $user = auth()->user();
        if (! $user->hasRole(UserRole::SUPER_ADMIN, UserRole::SCHOOL_ADMIN) && $announcement->created_by !== $user->id) {
            abort(403);
        }

        $schoolId = $announcement->school_id;
        $school = $schoolId ? School::find($schoolId) : School::current();

        $gradeLevels = GradeLevel::query()
            ->active()
            ->when($school && ! empty($school->educational_levels), function ($q) use ($school) {
                $q->whereIn('educational_level', (array) $school->educational_levels);
            })
            ->orderBy('order')
            ->get();

        $classrooms = Classroom::where('school_id', $schoolId)->get();

        return view('announcements.edit', compact('announcement', 'gradeLevels', 'classrooms'));
    }

    /**
     * Update announcement.
     */
    public function update(Request $request, SchoolAnnouncement $announcement): RedirectResponse
    {
        $user = $request->user();
        if (! $user->hasRole(UserRole::SUPER_ADMIN, UserRole::SCHOOL_ADMIN) && $announcement->created_by !== $user->id) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string', 'min:10'],
            'category' => ['required', 'string', 'in:general,academic,event,holiday,urgent'],
            'target_audience' => ['required', 'string', 'in:all,parents,students,teachers'],
            'grade_level_id' => ['nullable', 'exists:grade_levels,id'],
            'classroom_id' => ['nullable', 'exists:classrooms,id'],
            'status' => ['required', 'string', 'in:draft,published,archived'],
            'is_pinned' => ['nullable', 'boolean'],
            'attachment' => ['nullable', 'file', 'max:5120', 'mimes:pdf,doc,docx,jpg,jpeg,png'],
        ]);

        $updateData = [
            'title' => $validated['title'],
            'content' => $validated['content'],
            'category' => $validated['category'],
            'target_audience' => $validated['target_audience'],
            'grade_level_id' => $validated['grade_level_id'] ?? null,
            'classroom_id' => $validated['classroom_id'] ?? null,
            'status' => $validated['status'],
            'is_pinned' => $request->boolean('is_pinned'),
        ];

        if ($announcement->status !== 'published' && $validated['status'] === 'published') {
            $updateData['published_at'] = now();
        }

        if ($request->hasFile('attachment')) {
            if ($announcement->attachment_path) {
                Storage::disk('public')->delete($announcement->attachment_path);
            }
            $file = $request->file('attachment');
            $updateData['attachment_name'] = $file->getClientOriginalName();
            $updateData['attachment_path'] = $file->store('announcements/attachments', 'public');
        }

        $announcement->update($updateData);

        return redirect()->route('announcements.show', $announcement)
            ->with('success', 'Pengumuman sekolah berhasil diperbarui.');
    }

    /**
     * Delete announcement.
     */
    public function destroy(SchoolAnnouncement $announcement): RedirectResponse
    {
        $user = auth()->user();
        if (! $user->hasRole(UserRole::SUPER_ADMIN, UserRole::SCHOOL_ADMIN) && $announcement->created_by !== $user->id) {
            abort(403);
        }

        if ($announcement->attachment_path) {
            Storage::disk('public')->delete($announcement->attachment_path);
        }

        $announcement->delete();

        return redirect()->route('announcements.index')
            ->with('success', 'Pengumuman sekolah berhasil dihapus.');
    }
}
