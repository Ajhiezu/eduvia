<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\Invoice;
use App\Models\ParentLinkCode;
use App\Models\ParentStudentRelation;
use App\Models\ParentTeacherConversation;
use App\Models\Payment;
use App\Models\PaymentGatewayTransaction;
use App\Models\SchoolAnnouncement;
use App\Models\StudentReportCard;
use App\Models\User;
use App\Services\Payment\PaymentService;
use App\Services\SecureUploadService;
use App\Traits\ApiResponse;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ParentController extends Controller
{
    use ApiResponse;

    /**
     * Get list of children linked to this parent from real DB relationships.
     */
    public function children(Request $request): JsonResponse
    {
        $parent = $request->user();

        $relations = ParentStudentRelation::where('parent_id', $parent->id)
            ->where('status', 'active')
            ->with(['student.classroom', 'student.studentProfile'])
            ->get();

        $children = $relations->map(function ($rel) {
            $st = $rel->student;
            $classroom = $st?->classroom;
            $profile = $st?->studentProfile;

            return [
                'id' => $st->id,
                'name' => $st->name,
                'email' => $st->email,
                'nis' => $profile?->nis ?? '-',
                'nisn' => $profile?->nisn ?? '0081234567',
                'class_name' => $classroom?->name ?? 'VII-A',
                'relationship' => $rel->relationship_type ?? 'Orang Tua / Wali',
                'avatar' => $st->avatar ? asset('storage/'.$st->avatar) : null,
            ];
        });

        return $this->apiSuccess(
            data: $children,
            message: 'Daftar anak berhasil dimuat.'
        );
    }

    /**
     * Resolve target student belonging to this parent.
     */
    protected function resolveStudent(Request $request): ?User
    {
        $parentId = $request->user()->id;
        $studentId = $request->input('student_id');

        $query = ParentStudentRelation::where('parent_id', $parentId)->where('status', 'active');

        if ($studentId) {
            $query->where('student_id', $studentId);
        }

        $relation = $query->first() ?? ParentStudentRelation::where('parent_id', $parentId)->where('status', 'active')->first();

        return $relation?->student;
    }

    /**
     * Parent dashboard overview for the selected child.
     */
    public function dashboard(Request $request): JsonResponse
    {
        $student = $this->resolveStudent($request);

        if (! $student) {
            return $this->apiError('Tidak ada data anak yang terhubung ke akun Anda.', 404);
        }

        $classroom = $student->classroom;
        $profile = $student->studentProfile;

        // 1. Attendance statistics
        $attendanceRecords = AttendanceRecord::where('student_id', $student->id)->get();
        $totalSessions = $attendanceRecords->count();
        $presentCount = $attendanceRecords->where('status', 'present')->count();
        $sickCount = $attendanceRecords->where('status', 'sick')->count();
        $permitCount = $attendanceRecords->where('status', 'permit')->count();
        $absentCount = $attendanceRecords->where('status', 'absent')->count();
        $attendancePercent = $totalSessions > 0 ? round(($presentCount / $totalSessions) * 100, 1) : 100.0;

        // 2. Pending invoices (SPP)
        $pendingInvoice = Invoice::where('student_id', $student->id)
            ->whereIn('status', ['issued', 'overdue'])
            ->orderBy('due_date')
            ->first();

        // 3. Announcements for parents
        $announcements = SchoolAnnouncement::where('status', 'published')
            ->whereIn('target_audience', ['all', 'parents'])
            ->orderByDesc('is_pinned')
            ->orderByDesc('published_at')
            ->take(3)
            ->get()
            ->map(fn ($a) => [
                'id' => $a->id,
                'title' => $a->title,
                'category' => $a->category,
                'content' => $a->content,
                'date' => $a->published_at ? $a->published_at->translatedFormat('d M Y') : $a->created_at->translatedFormat('d M Y'),
                'is_pinned' => (bool) $a->is_pinned,
            ]);

        return $this->apiSuccess(
            data: [
                'child' => [
                    'id' => $student->id,
                    'name' => $student->name,
                    'nisn' => $profile?->nisn ?? '0081234567',
                    'class_name' => $classroom?->name ?? 'VII-A',
                ],
                'attendance_summary' => [
                    'percentage' => $attendancePercent,
                    'present' => $presentCount,
                    'sick' => $sickCount,
                    'permit' => $permitCount,
                    'absent' => $absentCount,
                ],
                'pending_invoice' => $pendingInvoice ? [
                    'id' => $pendingInvoice->id,
                    'title' => $pendingInvoice->title,
                    'amount' => (int) $pendingInvoice->total_amount,
                    'due_date' => Carbon::parse($pendingInvoice->due_date)->translatedFormat('d M Y'),
                    'status' => $pendingInvoice->status,
                ] : null,
                'announcements' => $announcements,
            ],
            message: 'Dashboard wali berhasil dimuat.'
        );
    }

    /**
     * Detailed attendance history for selected child.
     */
    public function attendance(Request $request): JsonResponse
    {
        $student = $this->resolveStudent($request);

        if (! $student) {
            return $this->apiError('Tidak ada data anak.', 404);
        }

        $records = AttendanceRecord::where('student_id', $student->id)
            ->with(['session'])
            ->get()
            ->sortByDesc(fn ($r) => $r->session?->date ?? $r->created_at)
            ->values();

        $history = $records->map(function ($r) {
            $date = $r->session?->date ? Carbon::parse($r->session->date) : $r->created_at;
            $statusLabel = match ($r->status) {
                'present' => 'Hadir Tepat Waktu',
                'sick' => 'Sakit (Surat Dokter)',
                'permit' => 'Izin',
                'absent' => 'Alpha',
                default => 'Hadir'
            };

            return [
                'id' => $r->id,
                'date' => $date->translatedFormat('l, d M Y'),
                'status' => $r->status,
                'status_label' => $statusLabel,
                'time' => $r->session?->start_time ? substr($r->session->start_time, 0, 5).' WIB' : '07:00 WIB',
                'notes' => $r->notes ?? '-',
            ];
        });

        $total = $records->count();
        $present = $records->where('status', 'present')->count();
        $sick = $records->where('status', 'sick')->count();
        $permit = $records->where('status', 'permit')->count();
        $absent = $records->where('status', 'absent')->count();

        return $this->apiSuccess(
            data: [
                'stats' => [
                    'present' => $present,
                    'sick' => $sick,
                    'permit' => $permit,
                    'absent' => $absent,
                ],
                'history' => $history,
            ],
            message: 'Data kehadiran berhasil dimuat.'
        );
    }

    /**
     * School invoices / SPP bills for selected child.
     */
    public function finance(Request $request): JsonResponse
    {
        $student = $this->resolveStudent($request);

        if (! $student) {
            return $this->apiError('Tidak ada data anak.', 404);
        }

        // Proactively sync any pending gateway transactions for this student
        $pendingTransactions = PaymentGatewayTransaction::where('student_id', $student->id)
            ->where('transaction_status', PaymentGatewayTransaction::STATUS_PENDING)
            ->where('created_at', '>=', now()->subHours(24))
            ->get();

        if ($pendingTransactions->isNotEmpty()) {
            $paymentService = app(PaymentService::class);
            foreach ($pendingTransactions as $pendingTx) {
                $paymentService->syncTransaction($pendingTx);
            }
        }

        $invoices = Invoice::where('student_id', $student->id)
            ->orderByDesc('due_date')
            ->with(['items'])
            ->get()
            ->map(fn ($inv) => [
                'id' => $inv->id,
                'invoice_number' => $inv->invoice_number,
                'title' => $inv->title,
                'total_amount' => (int) $inv->total_amount,
                'paid_amount' => (int) $inv->paid_amount,
                'due_date' => Carbon::parse($inv->due_date)->translatedFormat('d M Y'),
                'status' => $inv->status, // 'issued', 'paid', 'overdue'
                'notes' => $inv->notes,
            ]);

        return $this->apiSuccess(
            data: $invoices,
            message: 'Data tagihan berhasil dimuat.'
        );
    }

    /**
     * Claim invitation code to link student to parent account.
     */
    public function claimCode(Request $request): JsonResponse
    {
        $parent = $request->user();

        $validated = $request->validate([
            'code' => 'required|string|max:50',
            'relationship_type' => 'nullable|string|in:father,mother,guardian,other',
        ]);

        $inputCode = trim($validated['code']);

        $linkCode = ParentLinkCode::where('code', $inputCode)->first();

        if (! $linkCode) {
            return $this->apiError('Kode tautan siswa tidak valid atau tidak ditemukan.', 404);
        }

        if (! $linkCode->isUsable()) {
            return $this->apiError('Kode tautan telah kedaluwarsa atau batas penggunaan telah habis.', 400);
        }

        $student = $linkCode->student;
        if (! $student) {
            return $this->apiError('Data siswa terkait kode tautan tidak ditemukan.', 404);
        }

        // Check if relation already exists
        $existing = ParentStudentRelation::where('parent_id', $parent->id)
            ->where('student_id', $student->id)
            ->first();

        if ($existing && $existing->isActive()) {
            return $this->apiSuccess(
                data: [
                    'student_id' => $student->id,
                    'student_name' => $student->name,
                    'relationship' => $existing->relationshipLabel(),
                ],
                message: 'Akun Anda sudah terhubung aktif dengan siswa ini sebelumnya.'
            );
        }

        $relationType = $validated['relationship_type'] ?? $linkCode->relationship_type ?? ParentStudentRelation::TYPE_GUARDIAN;

        $relation = ParentStudentRelation::updateOrCreate(
            [
                'parent_id' => $parent->id,
                'student_id' => $student->id,
            ],
            [
                'school_id' => $student->school_id ?? $linkCode->school_id,
                'relationship_type' => $relationType,
                'status' => ParentStudentRelation::STATUS_ACTIVE,
                'verified_at' => now(),
                'verified_by' => $linkCode->created_by ?? $parent->id,
                'notes' => 'Ditautkan secara mandiri via kode klaim '.$linkCode->code,
            ]
        );

        // Update link code usage
        $linkCode->increment('used_count');
        $linkCode->update([
            'used_by' => $parent->id,
            'used_at' => now(),
        ]);

        return $this->apiSuccess(
            data: [
                'relation_id' => $relation->id,
                'student_id' => $student->id,
                'student_name' => $student->name,
                'nisn' => $student->studentProfile?->nisn ?? '-',
                'classroom' => $student->classroom?->name ?? '-',
                'relationship' => $relation->relationshipLabel(),
            ],
            message: 'Berhasil menghubungkan akun wali dengan ananda '.$student->name.'.'
        );
    }

    /**
     * Submit invoice payment with transfer proof upload.
     */
    public function submitInvoicePayment(Request $request, Invoice $invoice): JsonResponse
    {
        $parent = $request->user();

        // Verify parent is linked to invoice's student
        $isLinked = ParentStudentRelation::where('parent_id', $parent->id)
            ->where('student_id', $invoice->student_id)
            ->where('status', 'active')
            ->exists();

        if (! $isLinked) {
            return $this->apiError('Anda tidak memiliki akses ke tagihan siswa ini.', 403);
        }

        if ($invoice->status === Invoice::STATUS_PAID) {
            return $this->apiError('Tagihan ini sudah berstatus lunas.', 400);
        }

        $validated = $request->validate([
            'amount' => 'required|numeric|min:1000',
            'payment_method' => 'nullable|string|in:bank_transfer,cash,other',
            'reference_number' => 'nullable|string|max:100',
            'proof' => 'required|file|mimes:jpeg,png,jpg,pdf|max:10240', // 10MB max
            'notes' => 'nullable|string|max:500',
        ]);

        $uploadService = app(SecureUploadService::class);
        $stored = $uploadService->validateAndStore(
            $request->file('proof'),
            'payments/proofs',
            'public'
        );

        do {
            $payNumber = 'PAY-'.date('Ymd').'-'.strtoupper(Str::random(6));
        } while (Payment::where('payment_number', $payNumber)->exists());

        $payment = Payment::create([
            'school_id' => $invoice->school_id,
            'payment_number' => $payNumber,
            'invoice_id' => $invoice->id,
            'student_id' => $invoice->student_id,
            'paid_by' => $parent->id,
            'amount' => (int) $validated['amount'],
            'payment_date' => now()->toDateString(),
            'payment_method' => $validated['payment_method'] ?? Payment::METHOD_BANK_TRANSFER,
            'reference_number' => $validated['reference_number'] ?? null,
            'proof_path' => $stored['path'],
            'status' => Payment::STATUS_PENDING_VERIFICATION,
            'notes' => $validated['notes'] ?? 'Bukti pembayaran diunggah oleh wali murid via aplikasi mobile.',
            'recorded_by' => $parent->id,
        ]);

        return $this->apiSuccess(
            data: [
                'payment_id' => $payment->id,
                'payment_number' => $payment->payment_number,
                'invoice_number' => $invoice->invoice_number,
                'amount' => $payment->amount,
                'formatted_amount' => $payment->formatted_amount,
                'status' => $payment->status,
                'status_label' => $payment->status_label,
                'created_at' => $payment->created_at->toIso8601String(),
            ],
            message: 'Bukti pembayaran berhasil diunggah. Tagihan akan diverifikasi oleh bagian keuangan sekolah.'
        );
    }

    /**
     * Get published report cards for child.
     */
    public function reports(Request $request): JsonResponse
    {
        $student = $this->resolveStudent($request);

        if (! $student) {
            return $this->apiError('Data anak tidak ditemukan.', 404);
        }

        $reportCards = StudentReportCard::where('student_id', $student->id)
            ->where('status', StudentReportCard::STATUS_PUBLISHED)
            ->with(['academicYear', 'semester', 'classroom', 'homeroomTeacher', 'items'])
            ->orderByDesc('id')
            ->get()
            ->map(function ($rc) {
                $avgScore = $rc->calculateAverageScore();

                return [
                    'id' => $rc->id,
                    'academic_year' => $rc->academicYear?->name ?? 'Tahun Ajaran',
                    'semester' => $rc->semester?->name ?? 'Semester',
                    'classroom' => $rc->classroom?->name ?? 'Kelas',
                    'homeroom_teacher' => $rc->homeroomTeacher?->name ?? 'Wali Kelas',
                    'status' => $rc->status,
                    'status_label' => $rc->statusLabel(),
                    'average_score' => $avgScore,
                    'attendance_summary' => $rc->attendance_summary ?? [
                        'present' => 0,
                        'sick' => 0,
                        'permit' => 0,
                        'absent' => 0,
                    ],
                    'homeroom_notes' => $rc->homeroom_notes ?? 'Siswa menunjukkan perkembangan yang baik.',
                    'grades' => $rc->items->map(fn ($item) => [
                        'id' => $item->id,
                        'subject' => $item->subject_name,
                        'teacher' => $item->teacher_name,
                        'score' => (float) $item->final_score,
                        'passing_grade' => (float) $item->passing_grade,
                        'grade' => $item->predicate,
                        'is_passed' => (bool) $item->is_passed,
                        'description' => $item->achievement_description ?? '',
                    ]),
                ];
            });

        return $this->apiSuccess(
            data: [
                'child' => [
                    'id' => $student->id,
                    'name' => $student->name,
                    'nisn' => $student->studentProfile?->nisn ?? '0081234567',
                    'classroom' => $student->classroom?->name ?? '-',
                ],
                'reports' => $reportCards,
            ],
            message: 'Laporan rapor anak berhasil dimuat.'
        );
    }

    /**
     * Get parent-teacher communications.
     */
    public function conversations(Request $request): JsonResponse
    {
        $parent = $request->user();

        $conversations = ParentTeacherConversation::where('parent_id', $parent->id)
            ->with(['teacher', 'student', 'latestMessage'])
            ->orderByDesc('last_message_at')
            ->get()
            ->map(fn ($c) => [
                'id' => $c->id,
                'subject' => $c->subject,
                'child_name' => $c->student?->name ?? 'Ananda',
                'teacher_name' => $c->teacher?->name ?? 'Wali Kelas / Guru',
                'last_message' => $c->latestMessage?->body ?? 'Belum ada pesan',
                'last_message_time' => $c->last_message_at ? $c->last_message_at->translatedFormat('d M H:i') : null,
                'unread_count' => $c->unreadCountFor($parent),
                'status' => $c->status,
            ]);

        return $this->apiSuccess(
            data: $conversations,
            message: 'Daftar komunikasi dengan guru berhasil dimuat.'
        );
    }

    /**
     * Get messages in parent-teacher conversation.
     */
    public function conversationMessages(Request $request, ParentTeacherConversation $conversation): JsonResponse
    {
        $parent = $request->user();

        if (! $conversation->hasParticipant($parent)) {
            return $this->apiError('Anda tidak memiliki akses ke percakapan ini.', 403);
        }

        $messages = $conversation->messages()->with('sender')->get()->map(function ($m) use ($parent) {
            if ($m->sender_id !== $parent->id && ! $m->is_read) {
                $m->markAsRead();
            }

            return [
                'id' => $m->id,
                'sender_id' => $m->sender_id,
                'sender_name' => $m->sender?->name ?? 'Pengguna',
                'is_me' => $m->sender_id === $parent->id,
                'body' => $m->body,
                'attachment_url' => $m->attachmentUrl(),
                'attachment_name' => $m->attachment_name,
                'is_read' => $m->is_read,
                'created_at' => $m->created_at->translatedFormat('d M Y, H:i'),
            ];
        });

        return $this->apiSuccess(
            data: [
                'conversation' => [
                    'id' => $conversation->id,
                    'subject' => $conversation->subject,
                    'teacher' => $conversation->teacher?->name,
                    'child' => $conversation->student?->name,
                ],
                'messages' => $messages,
            ],
            message: 'Pesan percakapan berhasil dimuat.'
        );
    }

    /**
     * Send message in parent-teacher conversation.
     */
    public function sendConversationMessage(Request $request, ParentTeacherConversation $conversation): JsonResponse
    {
        $parent = $request->user();

        if (! $conversation->hasParticipant($parent)) {
            return $this->apiError('Anda tidak memiliki akses ke percakapan ini.', 403);
        }

        $validated = $request->validate([
            'body' => 'required|string|max:2000',
        ]);

        $message = $conversation->messages()->create([
            'sender_id' => $parent->id,
            'body' => $validated['body'],
            'is_read' => false,
        ]);

        $conversation->update(['last_message_at' => now()]);

        return $this->apiSuccess(
            data: [
                'id' => $message->id,
                'body' => $message->body,
                'created_at' => $message->created_at->toIso8601String(),
            ],
            message: 'Pesan berhasil dikirim.'
        );
    }
}
