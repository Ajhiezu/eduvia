<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\ApprovalDelegation;
use App\Models\ApprovalHistory;
use App\Models\ApprovalRequest;
use App\Models\ApprovalWorkflow;
use App\Models\Invoice;
use App\Models\ParentStudentRelation;
use App\Models\School;
use App\Models\User;
use App\Services\Workflow\ApprovalWorkflowService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UniversalApprovalWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected School $school;

    protected User $parent;

    protected User $student;

    protected User $teacher;

    protected User $principal;

    protected User $treasurer;

    protected Invoice $invoice;

    protected function setUp(): void
    {
        parent::setUp();

        $this->school = School::factory()->create([
            'name' => 'SMA Eduvia Jakarta',
        ]);

        $this->student = User::factory()->create([
            'school_id' => $this->school->id,
            'role' => 'student',
            'name' => 'Ananda Pratama',
        ]);

        $this->parent = User::factory()->create([
            'school_id' => $this->school->id,
            'role' => 'parent',
            'name' => 'Bapak Pratama',
        ]);

        ParentStudentRelation::create([
            'school_id' => $this->school->id,
            'parent_id' => $this->parent->id,
            'student_id' => $this->student->id,
            'relationship_type' => 'father',
            'status' => 'active',
        ]);

        $this->teacher = User::factory()->create([
            'school_id' => $this->school->id,
            'role' => 'teacher',
            'name' => 'Wali Kelas Ibu Siti',
        ]);

        $this->principal = User::factory()->create([
            'school_id' => $this->school->id,
            'role' => 'school_admin',
            'name' => 'Kepala Sekolah Pak Budi',
        ]);

        $this->treasurer = User::factory()->create([
            'school_id' => $this->school->id,
            'role' => 'super_admin',
            'name' => 'Bendahara Yayasan Pak Joko',
        ]);

        $academicYear = AcademicYear::factory()->create([
            'school_id' => $this->school->id,
        ]);

        $this->invoice = Invoice::create([
            'school_id' => $this->school->id,
            'academic_year_id' => $academicYear->id,
            'invoice_number' => 'INV-202610-001',
            'student_id' => $this->student->id,
            'title' => 'SPP Bulan Oktober 2026',
            'issue_date' => now()->toDateString(),
            'due_date' => now()->addDays(14)->toDateString(),
            'subtotal' => 500000,
            'total_amount' => 500000,
            'paid_amount' => 0,
            'status' => 'issued',
            'created_by' => $this->principal->id,
        ]);
    }

    public function test_parent_can_submit_tuition_fee_relief_request(): void
    {
        $response = $this->actingAs($this->parent, 'sanctum')->postJson('/api/v1/approvals/fee-relief', [
            'student_id' => $this->student->id,
            'invoice_id' => $this->invoice->id,
            'relief_type' => 'discount_percentage',
            'discount_percentage' => 50,
            'reason' => 'Keluarga terdampak musibah banjir dan membutuhkan keringanan biaya SPP.',
        ]);

        $response->assertStatus(201);
        $response->assertJsonPath('success', true);
        $this->assertDatabaseHas('approval_requests', [
            'requester_id' => $this->parent->id,
            'school_id' => $this->school->id,
            'current_step' => 1,
            'status' => ApprovalRequest::STATUS_PENDING,
        ]);
    }

    public function test_separation_of_duties_prevents_requester_from_approving_own_request(): void
    {
        // Teacher submits a letter/relief request
        $service = app(ApprovalWorkflowService::class);
        $request = $service->createRequest(
            requester: $this->teacher,
            workflowCode: ApprovalWorkflow::CODE_FEE_RELIEF,
            title: 'Pengajuan Keringanan',
            payload: ['student_id' => $this->student->id]
        );

        // Teacher tries to approve own request
        $response = $this->actingAs($this->teacher, 'sanctum')->postJson("/api/v1/approvals/{$request->id}/approve", [
            'comment' => 'Saya setujui pengajuan saya sendiri',
        ]);

        $response->assertStatus(403); // Gate denies action due to Separation of Duties
    }

    public function test_tiered_approval_flow_completes_through_all_three_steps(): void
    {
        $service = app(ApprovalWorkflowService::class);
        $request = $service->createRequest(
            requester: $this->parent,
            workflowCode: ApprovalWorkflow::CODE_FEE_RELIEF,
            title: 'Pengajuan Keringanan Siswa',
            payload: ['student_id' => $this->student->id]
        );

        $this->assertEquals(1, $request->current_step);
        $this->assertEquals(ApprovalRequest::STATUS_PENDING, $request->status);

        // Step 1: Teacher (Wali Kelas) approves
        $resp1 = $this->actingAs($this->teacher, 'sanctum')->postJson("/api/v1/approvals/{$request->id}/approve", [
            'comment' => 'Kondisi ekonomi keluarga telah divalidasi layak mendapat keringanan.',
        ]);
        $resp1->assertOk();
        $request->refresh();
        $this->assertEquals(2, $request->current_step);
        $this->assertEquals(ApprovalRequest::STATUS_PENDING, $request->status);

        // Step 2: Principal (Kepala Sekolah) approves
        $resp2 = $this->actingAs($this->principal, 'sanctum')->postJson("/api/v1/approvals/{$request->id}/approve", [
            'comment' => 'Disetujui sekolah untuk diteruskan ke yayasan.',
        ]);
        $resp2->assertOk();
        $request->refresh();
        $this->assertEquals(3, $request->current_step);

        // Step 3: Foundation Treasurer (superadmin) approves final
        $resp3 = $this->actingAs($this->treasurer, 'sanctum')->postJson("/api/v1/approvals/{$request->id}/approve", [
            'comment' => 'Disetujui yayasan potongan 50%.',
        ]);
        $resp3->assertOk();
        $request->refresh();
        $this->assertEquals(ApprovalRequest::STATUS_APPROVED, $request->status);
        $this->assertNotNull($request->completed_at);

        // Check history audit trail
        $this->assertEquals(4, $request->histories()->count()); // 1 submitted + 3 approvals
    }

    public function test_approver_can_reject_with_mandatory_comment(): void
    {
        $service = app(ApprovalWorkflowService::class);
        $request = $service->createRequest(
            requester: $this->parent,
            workflowCode: ApprovalWorkflow::CODE_FEE_RELIEF,
            title: 'Pengajuan Keringanan Siswa',
            payload: ['student_id' => $this->student->id]
        );

        $response = $this->actingAs($this->teacher, 'sanctum')->postJson("/api/v1/approvals/{$request->id}/reject", [
            'comment' => 'Dokumen pendukung SKTM tidak terlampir.',
        ]);

        $response->assertOk();
        $request->refresh();
        $this->assertEquals(ApprovalRequest::STATUS_REJECTED, $request->status);
        $this->assertNotNull($request->completed_at);

        $this->assertDatabaseHas('approval_histories', [
            'request_id' => $request->id,
            'action' => ApprovalHistory::ACTION_REJECTED,
            'comment' => 'Dokumen pendukung SKTM tidak terlampir.',
        ]);
    }

    public function test_delegation_allows_substitute_to_act_during_active_period(): void
    {
        $substituteTeacher = User::factory()->create([
            'school_id' => $this->school->id,
            'role' => 'student', // non-teacher originally
            'name' => 'Guru Pengganti Sementara',
        ]);

        // Teacher delegates authority to substitute
        ApprovalDelegation::create([
            'school_id' => $this->school->id,
            'delegator_id' => $this->teacher->id,
            'delegatee_id' => $substituteTeacher->id,
            'start_date' => now()->subDay()->toDateString(),
            'end_date' => now()->addDays(5)->toDateString(),
            'reason' => 'Sedang dinas luar kota',
            'is_active' => true,
        ]);

        $service = app(ApprovalWorkflowService::class);
        $request = $service->createRequest(
            requester: $this->parent,
            workflowCode: ApprovalWorkflow::CODE_FEE_RELIEF,
            title: 'Pengajuan Keringanan',
            payload: ['student_id' => $this->student->id]
        );

        // Substitute teacher acts on Step 1
        $response = $this->actingAs($substituteTeacher, 'sanctum')->postJson("/api/v1/approvals/{$request->id}/approve", [
            'comment' => 'Disetujui atas delegasi Ibu Siti',
        ]);

        $response->assertOk();
        $request->refresh();
        $this->assertEquals(2, $request->current_step);

        $this->assertDatabaseHas('approval_histories', [
            'request_id' => $request->id,
            'actor_id' => $substituteTeacher->id,
            'delegated_from_id' => $this->teacher->id,
            'action' => ApprovalHistory::ACTION_APPROVED,
        ]);
    }

    public function test_requester_can_cancel_pending_request(): void
    {
        $service = app(ApprovalWorkflowService::class);
        $request = $service->createRequest(
            requester: $this->parent,
            workflowCode: ApprovalWorkflow::CODE_FEE_RELIEF,
            title: 'Pengajuan Keringanan',
            payload: ['student_id' => $this->student->id]
        );

        $response = $this->actingAs($this->parent, 'sanctum')->postJson("/api/v1/approvals/{$request->id}/cancel", [
            'reason' => 'Orang tua membatalkan karena telah mendapat bantuan lain',
        ]);

        $response->assertOk();
        $request->refresh();
        $this->assertEquals(ApprovalRequest::STATUS_CANCELLED, $request->status);
        $this->assertEquals('Orang tua membatalkan karena telah mendapat bantuan lain', $request->cancellation_reason);
    }

    public function test_tenant_isolation_prevents_access_from_other_school(): void
    {
        $otherSchool = School::factory()->create(['name' => 'SMP Swasta Lain']);
        $otherPrincipal = User::factory()->create([
            'school_id' => $otherSchool->id,
            'role' => 'school_admin',
        ]);

        $service = app(ApprovalWorkflowService::class);
        $request = $service->createRequest(
            requester: $this->parent,
            workflowCode: ApprovalWorkflow::CODE_FEE_RELIEF,
            title: 'Pengajuan Keringanan',
            payload: ['student_id' => $this->student->id]
        );

        // Principal from another school cannot view or approve
        $response = $this->actingAs($otherPrincipal, 'sanctum')->getJson("/api/v1/approvals/{$request->id}");
        $this->assertTrue(in_array($response->status(), [403, 404], true));

        $actionResponse = $this->actingAs($otherPrincipal, 'sanctum')->postJson("/api/v1/approvals/{$request->id}/approve", [
            'comment' => 'Mencoba menyetujui sekolah lain',
        ]);
        $this->assertTrue(in_array($actionResponse->status(), [403, 404], true));
    }
}
