<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\ClassroomStudent;
use App\Models\ParentStudentRelation;
use App\Models\School;
use App\Models\SchoolAiQuota;
use App\Models\Semester;
use App\Models\User;
use App\Services\DataSubjectRightsService;
use App\Services\Pilot\PilotWaveManager;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class PilotReadinessTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_wave_two_requires_active_parent_consent_for_every_active_student(): void
    {
        $student = $this->enrolledStudent();
        $parent = User::factory()->parent()->create(['school_id' => $student->school_id, 'is_active' => true]);
        ParentStudentRelation::factory()->create(['school_id' => $student->school_id, 'student_id' => $student->id, 'parent_id' => $parent->id]);
        $manager = app(PilotWaveManager::class);

        $this->assertFalse($manager->preFlightCheck(2, $student->school_id)['can_activate']);

        $consent = app(DataSubjectRightsService::class)->recordConsent($student, 'child_data_processing', parent: $parent);
        $this->assertTrue($manager->preFlightCheck(2, $student->school_id)['can_activate']);

        $consent->revoke();
        $this->assertFalse($manager->preFlightCheck(2, $student->school_id)['can_activate']);
    }

    public function test_unrelated_parent_or_wrong_consent_type_cannot_pass_wave_two(): void
    {
        $student = $this->enrolledStudent();
        $parent = User::factory()->parent()->create(['school_id' => $student->school_id, 'is_active' => true]);
        $service = app(DataSubjectRightsService::class);
        $service->recordConsent($student, 'child_data_processing', parent: $parent);
        $manager = app(PilotWaveManager::class);

        $this->assertFalse($manager->preFlightCheck(2, $student->school_id)['can_activate']);

        ParentStudentRelation::factory()->create(['school_id' => $student->school_id, 'student_id' => $student->id, 'parent_id' => $parent->id]);
        $service->revokeConsent($student, 'child_data_processing');
        $service->recordConsent($student, 'privacy_policy', parent: $parent);

        $this->assertFalse($manager->preFlightCheck(2, $student->school_id)['can_activate']);
    }

    public function test_wave_six_checks_the_requested_school_quota(): void
    {
        $school = School::factory()->create();
        $otherSchool = School::factory()->create();
        SchoolAiQuota::create(['school_id' => $otherSchool->id, 'monthly_token_limit' => 10000, 'monthly_budget_idr' => 10000, 'quota_reset_at' => now()]);
        $manager = app(PilotWaveManager::class);

        $this->assertFalse($manager->preFlightCheck(6, $school->id)['can_activate']);

        SchoolAiQuota::create(['school_id' => $school->id, 'monthly_token_limit' => 10000, 'monthly_budget_idr' => 10000, 'quota_reset_at' => now()]);
        $this->assertTrue($manager->preFlightCheck(6, $school->id)['can_activate']);
    }

    private function enrolledStudent(): User
    {
        $school = School::factory()->create();
        $year = AcademicYear::factory()->create(['school_id' => $school->id, 'is_active' => true]);
        Semester::factory()->create(['academic_year_id' => $year->id, 'is_active' => true]);
        $classroom = Classroom::factory()->create(['school_id' => $school->id, 'academic_year_id' => $year->id]);
        User::factory()->teacher()->create(['school_id' => $school->id, 'is_active' => true]);
        $student = User::factory()->student()->create(['school_id' => $school->id, 'is_active' => true]);
        ClassroomStudent::factory()->create(['classroom_id' => $classroom->id, 'student_id' => $student->id, 'academic_year_id' => $year->id]);

        return $student;
    }
}
