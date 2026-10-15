<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\Classroom;
use App\Models\ClassroomStudent;
use App\Models\ConversationMessage;
use App\Models\ParentStudentRelation;
use App\Models\ParentTeacherConversation;
use App\Models\School;
use App\Models\TeachingAssignment;
use App\Models\User;
use App\Notifications\NewConversationMessageNotification;
use App\Services\ParentPortalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TeacherParentCommunicationController extends Controller
{
    public function __construct(
        protected ParentPortalService $parentPortalService
    ) {}

    /**
     * Display a listing of conversations.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        $query = ParentTeacherConversation::forParticipant($user)
            ->with(['parent', 'teacher', 'student', 'latestMessage'])
            ->latest('last_message_at')
            ->latest('id');

        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        if ($request->filled('search')) {
            $search = '%'.$request->query('search').'%';
            $query->where(function ($q) use ($search) {
                $q->where('subject', 'like', $search)
                    ->orWhereHas('parent', fn ($sub) => $sub->where('name', 'like', $search))
                    ->orWhereHas('teacher', fn ($sub) => $sub->where('name', 'like', $search))
                    ->orWhereHas('student', fn ($sub) => $sub->where('name', 'like', $search));
            });
        }

        $conversations = $query->paginate(15)->withQueryString();

        return view('communication.index', compact('conversations'));
    }

    /**
     * Show form to initiate a new conversation.
     */
    public function create(Request $request): View
    {
        $user = $request->user();

        $eligibleTeachers = collect();
        $eligibleParents = collect();
        $activeChildren = collect();

        if ($user->hasRole(UserRole::PARENT)) {
            $activeChildren = $this->parentPortalService->getActiveChildren($user);
            $selectedChildId = $request->filled('child_id') ? (int) $request->query('child_id') : $activeChildren->first()?->id;
            $selectedChild = $activeChildren->firstWhere('id', $selectedChildId);

            if ($selectedChild) {
                $enrollment = $this->parentPortalService->getActiveEnrollment($selectedChild);

                if ($enrollment) {
                    // Homeroom teacher
                    if ($enrollment->classroom?->homeroomTeacher) {
                        $eligibleTeachers->push($enrollment->classroom->homeroomTeacher);
                    }

                    // Subject teachers
                    $subjectTeachers = TeachingAssignment::where('classroom_id', $enrollment->classroom_id)
                        ->with('teacher')
                        ->get()
                        ->pluck('teacher')
                        ->filter();

                    $eligibleTeachers = $eligibleTeachers->concat($subjectTeachers)->unique('id');
                }
            }
        } elseif ($user->hasRole(UserRole::TEACHER)) {
            // Find all classrooms taught by this teacher
            $classroomIds = TeachingAssignment::where('teacher_id', $user->id)->pluck('classroom_id')
                ->merge(
                    Classroom::where('homeroom_teacher_id', $user->id)->pluck('id')
                )->unique()->all();

            $studentIds = ClassroomStudent::whereIn('classroom_id', $classroomIds)->pluck('student_id')->all();

            // Find verified parents of these students
            $eligibleParents = ParentStudentRelation::whereIn('student_id', $studentIds)
                ->where('status', ParentStudentRelation::STATUS_ACTIVE)
                ->with(['parent', 'student'])
                ->get();
        }

        return view('communication.create', compact(
            'activeChildren',
            'eligibleTeachers',
            'eligibleParents'
        ));
    }

    /**
     * Store new conversation and its first message.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        $schoolId = $user->school_id ?? School::current()?->id ?? 1;

        $validated = $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:3000'],
            'student_id' => ['required', 'exists:users,id'],
            'recipient_id' => ['required', 'exists:users,id'],
            'attachment' => ['nullable', 'file', 'max:5120', 'mimes:pdf,doc,docx,jpg,jpeg,png'],
        ], [
            'subject.required' => 'Topik konsultasi / subjek pesan wajib diisi.',
            'body.required' => 'Isi pesan wajib diisi.',
            'attachment.max' => 'Ukuran lampiran maksimal 5 MB.',
        ]);

        $student = User::findOrFail($validated['student_id']);
        $recipient = User::findOrFail($validated['recipient_id']);

        $parentId = null;
        $teacherId = null;

        if ($user->hasRole(UserRole::PARENT)) {
            $parentId = $user->id;
            $teacherId = $recipient->id;

            // Security check: Student must be active child of this parent
            $hasChild = ParentStudentRelation::where('parent_id', $user->id)
                ->where('student_id', $student->id)
                ->where('status', ParentStudentRelation::STATUS_ACTIVE)
                ->exists();

            if (! $hasChild) {
                abort(403, 'Akses ditolak: Anda hanya dapat mendiskusikan anak Anda sendiri yang terverifikasi.');
            }

            // Security check: Teacher must teach the student's classroom
            $enrollment = $this->parentPortalService->getActiveEnrollment($student);
            $teachesStudent = false;

            if ($enrollment) {
                if ($enrollment->classroom?->homeroom_teacher_id === $teacherId) {
                    $teachesStudent = true;
                } else {
                    $teachesStudent = TeachingAssignment::where('classroom_id', $enrollment->classroom_id)
                        ->where('teacher_id', $teacherId)
                        ->exists();
                }
            }

            if (! $teachesStudent && ! $user->isSuperAdmin()) {
                abort(403, 'Akses ditolak: Guru yang dipilih tidak memiliki hubungan akademik langsung dengan anak Anda.');
            }
        } elseif ($user->hasRole(UserRole::TEACHER)) {
            $teacherId = $user->id;
            $parentId = $recipient->id;

            // Security check: Parent must be verified parent of the student
            $isParentOfStudent = ParentStudentRelation::where('parent_id', $parentId)
                ->where('student_id', $student->id)
                ->where('status', ParentStudentRelation::STATUS_ACTIVE)
                ->exists();

            if (! $isParentOfStudent) {
                abort(403, 'Akses ditolak: Penerima pesan bukan orang tua resmi dari siswa ini.');
            }
        } else {
            // Admin initiating
            $parentId = $user->id;
            $teacherId = $recipient->id;
        }

        $conversation = ParentTeacherConversation::create([
            'school_id' => $schoolId,
            'student_id' => $student->id,
            'parent_id' => $parentId,
            'teacher_id' => $teacherId,
            'subject' => $validated['subject'],
            'status' => ParentTeacherConversation::STATUS_ACTIVE,
            'last_message_at' => now(),
        ]);

        $attachmentPath = null;
        $attachmentName = null;

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $attachmentName = $file->getClientOriginalName();
            $attachmentPath = $file->store('conversations/attachments', 'public');
        }

        $message = ConversationMessage::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $user->id,
            'body' => $validated['body'],
            'attachment_path' => $attachmentPath,
            'attachment_name' => $attachmentName,
            'is_read' => false,
        ]);

        // Send notification to recipient
        $recipient->notify(new NewConversationMessageNotification($message));

        return redirect()->route('conversations.show', $conversation)
            ->with('success', 'Percakapan berhasil dimulai dan pesan terkirim.');
    }

    /**
     * Display a specific conversation thread and mark unread messages as read.
     */
    public function show(ParentTeacherConversation $conversation): View
    {
        $user = auth()->user();

        // IDOR Authorization
        if (! $conversation->hasParticipant($user)) {
            abort(403, 'Akses ditolak: Anda bukan peserta dalam percakapan ini.');
        }

        // Mark incoming messages as read
        $conversation->messages()
            ->where('sender_id', '!=', $user->id)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        $conversation->load(['parent', 'teacher', 'student.studentProfile', 'messages.sender']);

        return view('communication.show', compact('conversation'));
    }

    /**
     * Reply to an existing conversation.
     */
    public function reply(Request $request, ParentTeacherConversation $conversation): RedirectResponse
    {
        $user = $request->user();

        // IDOR Authorization
        if (! $conversation->hasParticipant($user)) {
            abort(403, 'Akses ditolak: Anda tidak memiliki izin untuk membalas percakapan ini.');
        }

        if ($conversation->status === ParentTeacherConversation::STATUS_CLOSED) {
            return redirect()->back()->withErrors(['body' => 'Percakapan ini telah ditutup.']);
        }

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:3000'],
            'attachment' => ['nullable', 'file', 'max:5120', 'mimes:pdf,doc,docx,jpg,jpeg,png'],
        ], [
            'body.required' => 'Pesan tidak boleh kosong.',
            'attachment.max' => 'Ukuran lampiran maksimal 5 MB.',
        ]);

        $attachmentPath = null;
        $attachmentName = null;

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $attachmentName = $file->getClientOriginalName();
            $attachmentPath = $file->store('conversations/attachments', 'public');
        }

        $message = ConversationMessage::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $user->id,
            'body' => $validated['body'],
            'attachment_path' => $attachmentPath,
            'attachment_name' => $attachmentName,
            'is_read' => false,
        ]);

        $conversation->update([
            'last_message_at' => now(),
        ]);

        // Notify other participant
        $recipientId = (int) $user->id === (int) $conversation->parent_id
            ? $conversation->teacher_id
            : $conversation->parent_id;

        $recipient = User::find($recipientId);
        if ($recipient) {
            $recipient->notify(new NewConversationMessageNotification($message));
        }

        return redirect()->route('conversations.show', $conversation)
            ->with('success', 'Balasan pesan berhasil dikirim.');
    }

    /**
     * Close a conversation thread.
     */
    public function close(ParentTeacherConversation $conversation): RedirectResponse
    {
        $user = auth()->user();

        if (! $conversation->hasParticipant($user)) {
            abort(403);
        }

        $conversation->update([
            'status' => ParentTeacherConversation::STATUS_CLOSED,
        ]);

        return redirect()->back()
            ->with('success', 'Percakapan telah ditandai selesai/ditutup.');
    }
}
