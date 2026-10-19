<?php

namespace Tests\Feature\Security;

use App\Models\ParentTeacherConversation;
use App\Models\School;
use App\Models\SchoolMessage;
use App\Models\User;
use App\Services\TenantManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CrossTenantIsolationRegressionTest extends TestCase
{
    use RefreshDatabase;

    public function test_models_with_school_id_strictly_isolate_cross_tenant_data(): void
    {
        $schoolA = School::factory()->create();
        $schoolB = School::factory()->create();

        $teacherB = User::factory()->teacher()->create(['school_id' => $schoolB->id]);
        $parentB = User::factory()->parentRole()->create(['school_id' => $schoolB->id]);
        $studentB = User::factory()->student()->create(['school_id' => $schoolB->id]);

        // Create records in School B
        $msgB = SchoolMessage::create([
            'school_id' => $schoolB->id,
            'reference_number' => 'MSG-B-001',
            'name' => 'Whistleblower School B',
            'email' => 'whistle@schoolb.com',
            'subject' => 'Confidential B',
            'message' => 'Sensitive data B',
            'status' => 'unread',
        ]);

        $convB = ParentTeacherConversation::create([
            'school_id' => $schoolB->id,
            'student_id' => $studentB->id,
            'parent_id' => $parentB->id,
            'teacher_id' => $teacherB->id,
            'subject' => 'Private Student Discussion',
            'status' => 'active',
        ]);

        // Active context as School A
        $tenantManager = app(TenantManager::class);
        $tenantManager->setSchool($schoolA);

        $this->assertEquals(0, SchoolMessage::where('id', $msgB->id)->count(), 'School A must not be able to query SchoolMessage of School B');
        $this->assertEquals(0, ParentTeacherConversation::where('id', $convB->id)->count(), 'School A must not be able to query ParentTeacherConversation of School B');
    }
}
