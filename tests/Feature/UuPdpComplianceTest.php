<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\ParentStudentRelation;
use App\Models\School;
use App\Models\StudentProfile;
use App\Models\User;
use App\Services\DataSubjectRightsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UuPdpComplianceTest extends TestCase
{
    use RefreshDatabase;

    protected School $school;

    protected User $parent;

    protected User $student;

    protected DataSubjectRightsService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->school = School::create(['name' => 'SMA Eduvia PDP', 'code' => 'SCH-PDP', 'is_active' => true]);

        $this->parent = User::factory()->parent()->create(['school_id' => $this->school->id]);
        $this->student = User::factory()->student()->create(['school_id' => $this->school->id, 'name' => 'Dimas Wicaksono']);

        ParentStudentRelation::create([
            'school_id' => $this->school->id,
            'parent_id' => $this->parent->id,
            'student_id' => $this->student->id,
            'relationship_type' => 'father',
            'status' => ParentStudentRelation::STATUS_ACTIVE,
            'is_primary_contact' => true,
        ]);

        $this->service = new DataSubjectRightsService;
    }

    /**
     * Test 1: Pencatatan persetujuan pemrosesan data (consent) dengan relasi persetujuan orang tua.
     */
    public function test_consent_registration_and_parental_linkage(): void
    {
        // Catat persetujuan pemrosesan data anak oleh orang tua
        $consent = $this->service->recordConsent(
            user: $this->student,
            type: 'child_data_processing',
            version: '2026.1',
            isGranted: true,
            parent: $this->parent
        );

        $this->assertNotNull($consent->id);
        $this->assertEquals('child_data_processing', $consent->consent_type);
        $this->assertEquals((int) $this->parent->id, (int) $consent->parent_user_id);
        $this->assertTrue($consent->is_granted);

        // Verifikasi audit log pencatatan consent
        $audit = AuditLog::where('action', 'consent_recorded')->first();
        $this->assertNotNull($audit);
        $this->assertEquals((int) $this->school->id, (int) $audit->school_id);

        // Uji penarikan kembali persetujuan (revocation)
        $revoked = $this->service->revokeConsent($this->student, 'child_data_processing');
        $this->assertTrue($revoked);

        $consent->refresh();
        $this->assertFalse($consent->is_granted);
        $this->assertNotNull($consent->revoked_at);
    }

    /**
     * Test 2: Hak portabilitas dan akses data pribadi (UU PDP Pasal 7).
     */
    public function test_data_subject_portability_export(): void
    {
        StudentProfile::create([
            'user_id' => $this->student->id,
            'school_id' => $this->school->id,
            'nis' => '10293',
            'nisn' => '0091234567',
            'gender' => 'male',
            'birth_place' => 'Surabaya',
            'birth_date' => '2010-05-15',
            'address' => 'Jl. Pemuda No. 45 Surabaya',
            'parent_name' => 'Bapak Dimas',
        ]);

        $export = $this->service->exportUserData($this->student);

        $this->assertEquals('Dimas Wicaksono', $export['account']['name']);
        $this->assertEquals('10293', $export['profile']['nis']);
        $this->assertEquals('0091234567', $export['profile']['nisn']);
        $this->assertEquals('Jl. Pemuda No. 45 Surabaya', $export['profile']['address']);
        $this->assertArrayHasKey('consents', $export);
        $this->assertArrayHasKey('export_metadata', $export);

        // Verifikasi tercatat di audit log
        $audit = AuditLog::where('action', 'data_portability_export')->first();
        $this->assertNotNull($audit);
    }

    /**
     * Test 3: Hak penghapusan dan anonimisasi data (UU PDP Pasal 8) dengan retensi arsip.
     */
    public function test_data_subject_erasure_and_anonymization(): void
    {
        StudentProfile::create([
            'user_id' => $this->student->id,
            'school_id' => $this->school->id,
            'nis' => '10293',
            'nisn' => '0091234567',
            'gender' => 'male',
            'address' => 'Jl. Rahasia No. 12',
            'parent_name' => 'Orang Tua Asli',
        ]);

        $success = $this->service->anonymizeUserData($this->student);
        $this->assertTrue($success);

        $this->student->refresh();
        $profile = $this->student->studentProfile;

        // Nama dan email harus disamarkan
        $this->assertStringStartsWith('Eks-Peserta Didik (ANON-', $this->student->name);
        $this->assertStringContainsString('@anonymized.local', $this->student->email);
        $this->assertFalse((bool) $this->student->is_active);

        // Alamat dan data orang tua disamarkan
        $this->assertEquals('Disamarkan / Dihapus', $profile->address);
        $this->assertEquals('Disamarkan', $profile->parent_name);

        // Audit log mencatat tindakan anonimisasi
        $audit = AuditLog::where('action', 'data_erasure_anonymized')->first();
        $this->assertNotNull($audit);
    }
}
