<?php

namespace Tests\Feature\Security;

use App\Models\School;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApprovalAuthorizationAuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_parent_cannot_submit_fee_relief_for_unrelated_student(): void
    {
        $school = School::factory()->create();
        $parent = User::factory()->parentRole()->create(['school_id' => $school->id]);
        $unrelatedStudent = User::factory()->student()->create(['school_id' => $school->id]);

        Sanctum::actingAs($parent);

        $response = $this->postJson('/api/v1/approvals/fee-relief', [
            'student_id' => $unrelatedStudent->id,
            'relief_type' => 'scholarship',
            'reason' => 'Permohonan tidak sah.',
        ]);

        $response->assertStatus(403)
            ->assertJsonPath('success', false);
    }

    public function test_parent_cannot_delegate_approval_authority(): void
    {
        $school = School::factory()->create();
        $parent = User::factory()->parentRole()->create(['school_id' => $school->id]);
        $teacher = User::factory()->teacher()->create(['school_id' => $school->id]);

        Sanctum::actingAs($parent);

        $response = $this->postJson('/api/v1/approvals/delegations', [
            'delegatee_id' => $teacher->id,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(7)->toDateString(),
            'reason' => 'Percobaan pendelegasian wewenang oleh orang tua.',
        ]);

        $response->assertStatus(403)
            ->assertJsonPath('success', false);
    }

    public function test_parent_cannot_view_school_approval_delegations(): void
    {
        $school = School::factory()->create();
        $parent = User::factory()->parentRole()->create(['school_id' => $school->id]);

        Sanctum::actingAs($parent);

        $response = $this->getJson('/api/v1/approvals/delegations');

        $response->assertStatus(403)
            ->assertJsonPath('success', false);
    }

    public function test_staff_cannot_delegate_to_user_in_different_school(): void
    {
        $schoolA = School::factory()->create();
        $schoolB = School::factory()->create();

        $staffA = User::factory()->schoolAdmin()->create(['school_id' => $schoolA->id]);
        $staffB = User::factory()->schoolAdmin()->create(['school_id' => $schoolB->id]);

        Sanctum::actingAs($staffA);

        $response = $this->postJson('/api/v1/approvals/delegations', [
            'delegatee_id' => $staffB->id,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(7)->toDateString(),
            'reason' => 'Percobaan lintas sekolah.',
        ]);

        $response->assertStatus(422); // Validation error: delegatee_id not found in school A
    }
}
