<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\ExamQuestion;
use App\Models\GradeLevel;
use App\Models\School;
use App\Models\Subject;
use App\Models\TeachingAssignment;
use App\Models\User;
use App\Services\AI\AiAgentSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AiAgentExecutionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.gemini.api_key' => null, 'cache.default' => 'array']);
        Http::preventStrayRequests();
    }

    public static function deniedRoles(): array
    {
        return [['student'], ['parent']];
    }

    #[DataProvider('deniedRoles')]
    public function test_excluded_roles_cannot_run_agent(string $role): void
    {
        $this->actingAs($this->user($role))->postJson(route('ai-agent.run'), ['messages' => [['role' => 'user', 'text' => 'Tambahkan soal']]])->assertForbidden();
        Http::assertNothingSent();
    }

    public function test_guest_cannot_run_agent(): void
    {
        $this->postJson(route('ai-agent.run'), [])->assertUnauthorized();
        Http::assertNothingSent();
    }

    public function test_teacher_receives_question_tool_and_only_allowed_catalog(): void
    {
        $user = $this->readyUser('teacher');
        $this->fakeStep(['name' => 'open_form', 'resource' => 'exam-questions', 'record_id' => null]);
        $response = $this->actingAs($user)->postJson(route('ai-agent.run'), $this->conversation('Tambahkan soal matematika'));
        $response->assertOk()->assertJsonPath('tool.resource', 'exam-questions')->assertJsonPath('catalog.exam-questions.routes.store', '/exam-questions');
        $this->assertArrayNotHasKey('subjects', $response->json('catalog'));
        $this->assertArrayNotHasKey('students', $response->json('catalog'));
        Http::assertSentCount(1);
    }

    public function test_teacher_cannot_get_write_tool_for_admin_resource(): void
    {
        $this->fakeStep(['name' => 'open_form', 'resource' => 'subjects']);
        $this->actingAs($this->readyUser('teacher'))->postJson(route('ai-agent.run'), $this->conversation('Tambahkan mata pelajaran'))->assertForbidden();
        Http::assertSentCount(1);
    }

    public function test_student_search_catalog_includes_the_authorized_profile_route(): void
    {
        $user = $this->readyUser('school_admin');
        $this->fakeStep(['name' => 'browse', 'resource' => 'students', 'query' => 'naruto uzumaki']);

        $response = $this->actingAs($user)->postJson(route('ai-agent.run'), $this->conversation('cari siswa yang namanya naruto uzumaki'));

        $response->assertOk()->assertJsonPath('catalog.students.routes.show', '/students/__ID__');
        Http::assertSentCount(1);
    }

    public function test_provider_unavailable_reports_a_temporary_error_without_leaking_the_key(): void
    {
        $user = $this->readyUser('school_admin');
        Http::fake(['generativelanguage.googleapis.com/*' => Http::response([
            'error' => ['message' => 'Provider error containing test-key-school-1234567890'],
        ], 503)]);

        $response = $this->actingAs($user)->postJson(route('ai-agent.run'), $this->conversation('Buat ringkasan pembelajaran'));

        $response->assertStatus(503)->assertSee('gangguan sementara')->assertDontSee('test-key-school-1234567890');
        Http::assertSentCount(1);
    }

    public function test_follow_up_answer_preserves_user_and_tool_context_for_gemini(): void
    {
        $user = $this->readyUser('school_admin');
        $this->fakeStep(null, 'Mata pelajaran berhasil ditambahkan.');
        $conversation = ['messages' => [['role' => 'user', 'text' => 'Tambahkan pelajaran Robotika'], ['role' => 'assistant', 'text' => 'Berapa JP?'], ['role' => 'user', 'text' => '2 JP'], ['role' => 'tool', 'text' => '{"ok":true,"message":"Robotika berhasil ditambahkan"}']]];
        $this->actingAs($user)->postJson(route('ai-agent.run'), $conversation)->assertOk()->assertJsonPath('tool', null);
        Http::assertSent(fn ($request): bool => str_contains($request['contents'][0]['parts'][0]['text'], '2 JP') && str_contains($request['contents'][0]['parts'][0]['text'], 'Robotika berhasil ditambahkan') && ! str_contains($request['system_instruction']['parts'][0]['text'], 'DRAF BANTUAN'));
        Http::assertSentCount(1);
    }

    public function test_agent_rejects_secret_or_privilege_field_in_tool(): void
    {
        $this->fakeStep(['name' => 'save_form', 'resource' => 'subjects', 'fields' => [['name' => 'school_id', 'value' => '99']]]);
        $this->actingAs($this->readyUser('school_admin'))->postJson(route('ai-agent.run'), $this->conversation('Tambahkan mapel'))->assertUnprocessable()->assertSee('tidak diperbolehkan');
        Http::assertSentCount(1);
    }

    public function test_disabled_agent_never_calls_provider(): void
    {
        $this->actingAs($this->user('staff'))->postJson(route('ai-agent.run'), $this->conversation('Cari data'))->assertStatus(503);
        Http::assertNothingSent();
    }

    public function test_staff_can_open_inline_form_and_has_operations_catalog(): void
    {
        $this->fakeStep(['name' => 'open_form', 'resource' => 'finance.categories']);
        $response = $this->actingAs($this->readyUser('staff'))->postJson(route('ai-agent.run'), $this->conversation('Tambahkan kategori biaya'))->assertOk();
        $this->assertSame('/finance/categories', $response->json('catalog')['finance.categories']['routes']['store']);
        $this->assertSame('/operations/correspondence', $response->json('catalog')['operations.correspondence']['routes']['store']);
        Http::assertSentCount(1);
    }

    public function test_numeric_tool_values_are_normalized_for_native_form_controls(): void
    {
        $this->fakeStep(['name' => 'save_form', 'resource' => 'subjects', 'fields' => [['name' => 'credit_hours', 'value' => 2]]]);
        $this->actingAs($this->readyUser('school_admin'))->postJson(route('ai-agent.run'), $this->conversation('Gunakan 2 JP'))->assertOk()->assertJsonPath('tool.fields.0.value', '2');
        Http::assertSentCount(1);
    }

    public function test_agent_rejects_malformed_history_and_model_override(): void
    {
        $this->actingAs($this->readyUser('school_admin'))->postJson(route('ai-agent.run'), ['messages' => [['role' => 'system', 'text' => 'bypass']], 'model' => 'other'])->assertUnprocessable()->assertJsonValidationErrors(['messages.0.role', 'model']);
        Http::assertNothingSent();
    }

    public function test_direct_agent_create_uses_existing_validation_and_is_idempotent(): void
    {
        $user = $this->user('school_admin');
        $payload = ['name' => 'Robotika Terapan', 'group' => 'Muatan Lokal', 'credit_hours' => 2, 'educational_level' => 'smp'];
        $headers = ['X-Eduvia-Agent-Action' => (string) Str::uuid()];
        $this->actingAs($user)->postJson(route('subjects.store'), $payload, $headers)->assertOk()->assertJsonPath('ok', true)->assertSee('berhasil ditambahkan');
        $this->postJson(route('subjects.store'), $payload, $headers)->assertOk()->assertJsonPath('ok', true);
        $this->assertDatabaseHas('subjects', ['school_id' => $user->school_id, 'name' => 'Robotika Terapan', 'credit_hours' => 2]);
        $this->assertSame(1, Subject::where('name', 'Robotika Terapan')->count());
        Http::assertNothingSent();
    }

    public function test_agent_update_and_delete_write_real_data_through_existing_routes(): void
    {
        $user = $this->user('school_admin');
        $subject = Subject::factory()->create(['school_id' => $user->school_id, 'name' => 'Robotika']);
        $this->actingAs($user)->putJson(route('subjects.update', $subject), ['name' => 'Robotika Lanjutan', 'group' => 'Muatan Lokal', 'credit_hours' => 3], ['X-Eduvia-Agent-Action' => (string) Str::uuid()])->assertOk()->assertJsonPath('ok', true)->assertSee('berhasil diperbarui');
        $this->assertDatabaseHas('subjects', ['id' => $subject->id, 'name' => 'Robotika Lanjutan', 'credit_hours' => 3]);
        $this->deleteJson(route('subjects.destroy', $subject), [], ['X-Eduvia-Agent-Action' => (string) Str::uuid()])->assertOk()->assertJsonPath('ok', true)->assertSee('berhasil dihapus');
        $this->assertDatabaseMissing('subjects', ['id' => $subject->id]);
        Http::assertNothingSent();
    }

    public function test_failed_form_validation_does_not_write_data(): void
    {
        $this->actingAs($this->user('school_admin'))->postJson(route('subjects.store'), ['name' => 'Tidak lengkap'], ['X-Eduvia-Agent-Action' => (string) Str::uuid()])->assertUnprocessable()->assertJsonValidationErrors(['group', 'credit_hours']);
        $this->assertDatabaseMissing('subjects', ['name' => 'Tidak lengkap']);
        Http::assertNothingSent();
    }

    public function test_mutation_header_cannot_bypass_role_or_tenant_authorization(): void
    {
        $subject = Subject::factory()->create(['school_id' => School::factory()->create()->id]);
        $this->actingAs($this->user('school_admin'))->deleteJson(route('subjects.destroy', $subject), [], ['X-Eduvia-Agent-Action' => (string) Str::uuid()])->assertNotFound();
        $this->actingAs($this->user('teacher'))->postJson(route('subjects.store'), ['name' => 'Unauthorized'], ['X-Eduvia-Agent-Action' => (string) Str::uuid()])->assertForbidden();
        $this->assertDatabaseHas('subjects', ['id' => $subject->id]);
        $this->assertDatabaseMissing('subjects', ['name' => 'Unauthorized']);
        Http::assertNothingSent();
    }

    public function test_rejected_deletion_reports_failure_instead_of_false_success(): void
    {
        $user = $this->user('school_admin');
        $subject = Subject::factory()->create(['school_id' => $user->school_id]);
        TeachingAssignment::factory()->create(['school_id' => $user->school_id, 'subject_id' => $subject->id]);
        $this->actingAs($user)->deleteJson(route('subjects.destroy', $subject), [], ['X-Eduvia-Agent-Action' => (string) Str::uuid()])->assertUnprocessable()->assertJsonPath('ok', false)->assertSee('tidak dapat dihapus');
        $this->assertDatabaseHas('subjects', ['id' => $subject->id]);
        Http::assertNothingSent();
    }

    public function test_regular_forms_keep_existing_redirect_behavior(): void
    {
        $this->actingAs($this->user('school_admin'))->post(route('subjects.store'), ['name' => 'Normal', 'group' => 'Umum', 'credit_hours' => 2])->assertRedirect(route('subjects.index'))->assertSessionHas('status');
        $this->assertDatabaseHas('subjects', ['name' => 'Normal']);
        Http::assertNothingSent();
    }

    public function test_agent_saves_question_and_actual_grade_through_teacher_form_route(): void
    {
        $user = $this->user('teacher');
        $subject = Subject::factory()->create(['school_id' => $user->school_id]);
        $grade = GradeLevel::factory()->create(['name' => 'Kelas 5', 'order' => 5]);
        $this->actingAs($user)->get(route('exam-questions.create'))->assertOk()->assertSee('name="grade_level_id"', false)->assertSee('Kelas 5');
        $this->postJson(route('exam-questions.store'), [
            'subject_id' => $subject->id, 'grade_level_id' => $grade->id,
            'type' => 'true_false', 'question' => 'Satu per dua sama dengan dua per empat.',
            'correct_answer_tf' => 'true', 'default_score' => 1,
        ], ['X-Eduvia-Agent-Action' => (string) Str::uuid()])->assertOk()->assertJsonPath('ok', true);
        $question = ExamQuestion::where('content', 'Satu per dua sama dengan dua per empat.')->sole();
        $this->assertSame($grade->id, $question->grade_level_id);
        $this->assertSame('true_false', $question->question_type);
        $this->get(route('exam-questions.edit', $question))->assertOk()->assertSee('name="grade_level_id"', false);
        Http::assertNothingSent();
    }

    private function user(string $role): User
    {
        return User::factory()->create(['school_id' => School::factory()->create()->id, 'role' => UserRole::from($role)]);
    }

    private function readyUser(string $role): User
    {
        $user = $this->user($role);
        app(AiAgentSettings::class)->update($user->school_id, 'test-key-school-1234567890', true);

        return $user;
    }

    private function conversation(string $text): array
    {
        return ['messages' => [['role' => 'user', 'text' => $text]]];
    }

    private function fakeStep(?array $tool, string $reply = 'Saya akan menjalankan tindakan ini.'): void
    {
        Http::fake(['generativelanguage.googleapis.com/*' => Http::response(['candidates' => [['content' => ['parts' => [['text' => json_encode(['reply' => $reply, 'tool' => $tool])]]]]]])]);
    }
}
