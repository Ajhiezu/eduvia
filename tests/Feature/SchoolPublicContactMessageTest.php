<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Mail\NewContactMessageMail;
use App\Models\School;
use App\Models\SchoolMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SchoolPublicContactMessageTest extends TestCase
{
    use RefreshDatabase;

    protected School $schoolA;

    protected School $schoolB;

    protected User $adminA;

    protected User $adminB;

    protected User $superAdmin;

    protected User $teacher;

    protected User $student;

    protected function setUp(): void
    {
        parent::setUp();

        $this->schoolA = School::factory()->create([
            'name' => 'SMA Eduvia Jakarta',
            'slug' => 'eduvia-jakarta',
            'email' => 'admin@eduvia-jakarta.sch.id',
            'public_website_enabled' => true,
        ]);

        $this->schoolB = School::factory()->create([
            'name' => 'SMP Eduvia Surabaya',
            'slug' => 'eduvia-surabaya',
            'email' => 'admin@eduvia-surabaya.sch.id',
            'public_website_enabled' => true,
        ]);

        $this->adminA = User::factory()->create([
            'school_id' => $this->schoolA->id,
            'role' => UserRole::SCHOOL_ADMIN,
        ]);

        $this->adminB = User::factory()->create([
            'school_id' => $this->schoolB->id,
            'role' => UserRole::SCHOOL_ADMIN,
        ]);

        $this->superAdmin = User::factory()->create([
            'school_id' => null,
            'role' => UserRole::SUPER_ADMIN,
        ]);

        $this->teacher = User::factory()->create([
            'school_id' => $this->schoolA->id,
            'role' => UserRole::TEACHER,
        ]);

        $this->student = User::factory()->create([
            'school_id' => $this->schoolA->id,
            'role' => UserRole::STUDENT,
        ]);
    }

    // =========================================================================
    // 1. PUBLIC CONTACT FORM — POSITIVE CASES
    // =========================================================================

    public function test_public_contact_page_renders_successfully(): void
    {
        $response = $this->get(route('school.public.contact', ['school' => $this->schoolA->slug]));

        $response->assertStatus(200);
        $response->assertSee('Hubungi SMA Eduvia Jakarta');
        $response->assertSee('Formulir Pesan & Pertanyaan', false);
        $response->assertSee('admin@eduvia-jakarta.sch.id');
    }

    public function test_public_contact_page_returns_404_if_school_website_disabled(): void
    {
        $this->schoolA->update(['public_website_enabled' => false]);

        $response = $this->get(route('school.public.contact', ['school' => $this->schoolA->slug]));

        $response->assertStatus(404);
    }

    public function test_visitor_can_submit_valid_contact_message_successfully(): void
    {
        Mail::fake();

        $payload = [
            'name' => 'Ahmad Dahlan',
            'email' => 'ahmad.dahlan@example.com',
            'phone' => '081234567890',
            'category' => 'informasi_sekolah',
            'subject' => 'Pertanyaan Kurikulum Merdeka di SMA Eduvia',
            'message' => 'Selamat siang, saya ingin menanyakan mengenai penerapan proyek P5 di sekolah ini.',
            'privacy_consent' => '1',
        ];

        $response = $this->post(route('school.public.contact.submit', ['school' => $this->schoolA->slug]), $payload);

        $response->assertRedirect(route('school.public.contact', ['school' => $this->schoolA->slug]));
        $response->assertSessionHas('contact_success', true);
        $response->assertSessionHas('reference_number');

        $this->assertDatabaseHas('school_messages', [
            'school_id' => $this->schoolA->id,
            'name' => 'Ahmad Dahlan',
            'email' => 'ahmad.dahlan@example.com',
            'phone' => '081234567890',
            'category' => 'informasi_sekolah',
            'subject' => 'Pertanyaan Kurikulum Merdeka di SMA Eduvia',
            'status' => 'new',
            'is_read' => false,
        ]);

        Mail::assertQueued(NewContactMessageMail::class, function ($mail) {
            return $mail->hasTo('admin@eduvia-jakarta.sch.id');
        });
    }

    public function test_contact_message_is_tied_to_correct_school_id_and_generates_unique_reference(): void
    {
        $payload = [
            'name' => 'Dewi Sartika',
            'email' => 'dewi@example.com',
            'phone' => '081299998888',
            'category' => 'akademik',
            'subject' => 'Peminatan Kelas 10',
            'message' => 'Apakah peminatan sains sudah dimulai sejak semester pertama?',
            'privacy_consent' => '1',
        ];

        $this->post(route('school.public.contact.submit', ['school' => $this->schoolA->slug]), $payload);

        $message = SchoolMessage::where('email', 'dewi@example.com')->first();
        $this->assertNotNull($message);
        $this->assertEquals($this->schoolA->id, $message->school_id);
        $this->assertStringStartsWith('MSG-', $message->reference_number);

        // Ensure School B is untouched
        $this->assertEquals(0, SchoolMessage::where('school_id', $this->schoolB->id)->count());
    }

    public function test_visitor_cannot_tamper_school_id_via_request(): void
    {
        $payload = [
            'school_id' => $this->schoolB->id, // Tamper attempt
            'name' => 'Hacker Pengacau',
            'email' => 'hacker@example.com',
            'category' => 'lainnya',
            'subject' => 'Percobaan Tampering ID',
            'message' => 'Mencoba mengarahkan pesan ke sekolah lain.',
            'privacy_consent' => '1',
        ];

        $this->post(route('school.public.contact.submit', ['school' => $this->schoolA->slug]), $payload);

        $saved = SchoolMessage::where('email', 'hacker@example.com')->first();
        $this->assertNotNull($saved);
        // Must strictly bind to School A, ignore supplied school_id
        $this->assertEquals($this->schoolA->id, $saved->school_id);
        $this->assertNotEquals($this->schoolB->id, $saved->school_id);
    }

    public function test_message_is_safely_saved_even_when_email_sending_throws_exception(): void
    {
        Mail::shouldReceive('to->send')->andThrow(new \RuntimeException('SMTP Connection timeout'));

        $payload = [
            'name' => 'Rina Gunawan',
            'email' => 'rina@example.com',
            'category' => 'ppdb',
            'subject' => 'Tes resilience email failure',
            'message' => 'Pesan harus tetap tersimpan meskipun server email sedang down.',
            'privacy_consent' => '1',
        ];

        $response = $this->post(route('school.public.contact.submit', ['school' => $this->schoolA->slug]), $payload);

        $response->assertRedirect(route('school.public.contact', ['school' => $this->schoolA->slug]));
        $response->assertSessionHas('contact_success', true);

        $this->assertDatabaseHas('school_messages', [
            'school_id' => $this->schoolA->id,
            'email' => 'rina@example.com',
        ]);
    }

    // =========================================================================
    // 2. PUBLIC CONTACT FORM — NEGATIVE & VALIDATION CASES
    // =========================================================================

    public function test_contact_form_validates_required_fields_and_formats(): void
    {
        $response = $this->post(route('school.public.contact.submit', ['school' => $this->schoolA->slug]), [
            'name' => '',
            'email' => 'not-an-email',
            'subject' => '',
            'message' => '',
            'category' => '',
        ]);

        $response->assertSessionHasErrors(['name', 'email', 'subject', 'message', 'category', 'privacy_consent']);
    }

    public function test_contact_form_validates_max_lengths(): void
    {
        $response = $this->post(route('school.public.contact.submit', ['school' => $this->schoolA->slug]), [
            'name' => str_repeat('A', 151),
            'email' => str_repeat('a', 145).'@example.com', // > 150
            'phone' => str_repeat('1', 35),
            'subject' => str_repeat('S', 205),
            'message' => str_repeat('M', 3005),
            'category' => 'akademik',
            'privacy_consent' => '1',
        ]);

        $response->assertSessionHasErrors(['name', 'email', 'phone', 'subject', 'message']);
    }

    public function test_contact_form_validates_valid_category_enum(): void
    {
        $response = $this->post(route('school.public.contact.submit', ['school' => $this->schoolA->slug]), [
            'name' => 'Valid Name',
            'email' => 'valid@example.com',
            'subject' => 'Valid Subject',
            'message' => 'Valid message',
            'category' => 'kategori_palsu_random',
            'privacy_consent' => '1',
        ]);

        $response->assertSessionHasErrors(['category']);
    }

    public function test_contact_form_requires_privacy_consent(): void
    {
        $response = $this->post(route('school.public.contact.submit', ['school' => $this->schoolA->slug]), [
            'name' => 'Valid Name',
            'email' => 'valid@example.com',
            'subject' => 'Valid Subject',
            'message' => 'Valid message',
            'category' => 'akademik',
            'privacy_consent' => '0',
        ]);

        $response->assertSessionHasErrors(['privacy_consent']);
    }

    public function test_anti_spam_honeypot_drops_bot_submissions(): void
    {
        $payload = [
            'hp_website' => 'http://spam-link.ru', // Honeypot filled by automated bot
            'name' => 'Spam Bot',
            'email' => 'bot@spammer.com',
            'category' => 'lainnya',
            'subject' => 'Buy Crypto Now',
            'message' => 'Visit my suspicious website http://spam-link.ru',
            'privacy_consent' => '1',
        ];

        $response = $this->post(route('school.public.contact.submit', ['school' => $this->schoolA->slug]), $payload);

        // Honeypot silently redirects without persisting spam into database
        $response->assertRedirect(route('school.public.contact', ['school' => $this->schoolA->slug]));
        $this->assertDatabaseMissing('school_messages', [
            'email' => 'bot@spammer.com',
        ]);
    }

    public function test_xss_payload_in_message_is_safely_escaped_on_render(): void
    {
        $xssScript = '<script>alert("XSS-ATTACK")</script><img src="x" onerror="alert(1)">';

        $message = SchoolMessage::create([
            'school_id' => $this->schoolA->id,
            'reference_number' => 'MSG-TEST-XSS01',
            'name' => 'Attacker <script>evil()</script>',
            'email' => 'attacker@example.com',
            'category' => 'lainnya',
            'subject' => 'XSS Subject <b onmouseover=alert(1)>Hover</b>',
            'message' => $xssScript,
            'status' => 'new',
            'is_read' => false,
        ]);

        $response = $this->actingAs($this->adminA)->get(route('website.messages.show', $message));

        $response->assertStatus(200);
        // Raw unescaped executable script tags must NOT appear in output
        $response->assertDontSee('<script>alert("XSS-ATTACK")</script>', false);
        $response->assertDontSee('<img src="x" onerror="alert(1)">', false);
        // HTML entities must be escaped
        $response->assertSee('&lt;script&gt;alert(&quot;XSS-ATTACK&quot;)&lt;/script&gt;', false);
    }

    // =========================================================================
    // 3. ADMIN DASHBOARD & MESSAGE MANAGEMENT — POSITIVE CASES
    // =========================================================================

    public function test_admin_can_view_list_of_their_schools_messages_with_stats(): void
    {
        SchoolMessage::create([
            'school_id' => $this->schoolA->id,
            'reference_number' => 'MSG-A-001',
            'name' => 'Pesan Pertama Sekolah A',
            'email' => 'pengirim1@example.com',
            'category' => 'akademik',
            'subject' => 'Pertanyaan Jadwal Pelajaran',
            'message' => 'Bagaimana jadwal pelajaran kelas 10?',
            'status' => 'new',
            'is_read' => false,
        ]);

        SchoolMessage::create([
            'school_id' => $this->schoolB->id,
            'reference_number' => 'MSG-B-001',
            'name' => 'Pesan Sekolah B',
            'email' => 'pengirim2@example.com',
            'category' => 'akademik',
            'subject' => 'Pesan Rahasia Sekolah B',
            'message' => 'Pesan milik sekolah B saja.',
            'status' => 'new',
            'is_read' => false,
        ]);

        $response = $this->actingAs($this->adminA)->get(route('website.messages.index'));

        $response->assertStatus(200);
        $response->assertSee('Pertanyaan Jadwal Pelajaran');
        $response->assertSee('Pesan Pertama Sekolah A');
        // Tenant isolation: Admin A must NEVER see School B messages
        $response->assertDontSee('Pesan Rahasia Sekolah B');
        $response->assertDontSee('MSG-B-001');
    }

    public function test_admin_can_search_messages_by_keyword_and_filter_by_status_and_category(): void
    {
        SchoolMessage::create([
            'school_id' => $this->schoolA->id,
            'reference_number' => 'MSG-SEARCH-01',
            'name' => 'Bambang Sudirman',
            'email' => 'bambang@example.com',
            'category' => 'ppdb',
            'subject' => 'Info Jalur Afirmasi',
            'message' => 'Syarat KIP untuk PPDB',
            'status' => 'new',
            'is_read' => false,
        ]);

        SchoolMessage::create([
            'school_id' => $this->schoolA->id,
            'reference_number' => 'MSG-SEARCH-02',
            'name' => 'Citra Lestari',
            'email' => 'citra@example.com',
            'category' => 'ekstrakurikuler',
            'subject' => 'Info Paduan Suara',
            'message' => 'Jadwal latihan vokal',
            'status' => 'resolved',
            'is_read' => true,
        ]);

        // Search by name
        $response = $this->actingAs($this->adminA)->get(route('website.messages.index', ['search' => 'Bambang']));
        $response->assertSee('Info Jalur Afirmasi');
        $response->assertDontSee('Info Paduan Suara');

        // Filter by category
        $response = $this->actingAs($this->adminA)->get(route('website.messages.index', ['category' => 'ekstrakurikuler']));
        $response->assertSee('Info Paduan Suara');
        $response->assertDontSee('Info Jalur Afirmasi');

        // Filter by status
        $response = $this->actingAs($this->adminA)->get(route('website.messages.index', ['status' => 'resolved']));
        $response->assertSee('Info Paduan Suara');
        $response->assertDontSee('Info Jalur Afirmasi');
    }

    public function test_admin_can_view_message_detail_and_it_is_marked_as_read(): void
    {
        $message = SchoolMessage::create([
            'school_id' => $this->schoolA->id,
            'reference_number' => 'MSG-DETAIL-01',
            'name' => 'Bapak Joko',
            'email' => 'joko@example.com',
            'phone' => '0812345678',
            'category' => 'akademik',
            'subject' => 'Tanya Nilai Rapor',
            'message' => 'Apakah rapor semester ganjil sudah dapat diunduh?',
            'status' => 'new',
            'is_read' => false,
            'read_at' => null,
        ]);

        $this->assertFalse($message->is_read);

        $response = $this->actingAs($this->adminA)->get(route('website.messages.show', $message));

        $response->assertStatus(200);
        $response->assertSee('Bapak Joko');
        $response->assertSee('Tanya Nilai Rapor');
        $response->assertSee('Apakah rapor semester ganjil sudah dapat diunduh?');

        $message->refresh();
        $this->assertTrue($message->is_read);
        $this->assertNotNull($message->read_at);
    }

    public function test_admin_can_update_message_status(): void
    {
        $message = SchoolMessage::create([
            'school_id' => $this->schoolA->id,
            'reference_number' => 'MSG-STATUS-01',
            'name' => 'Ibu Maria',
            'email' => 'maria@example.com',
            'category' => 'ppdb',
            'subject' => 'Jadwal Wawancara',
            'message' => 'Kapan tes wawancara gelombang 1?',
            'status' => 'new',
            'is_read' => true,
        ]);

        $response = $this->actingAs($this->adminA)->put(route('website.messages.update-status', $message), [
            'status' => 'resolved',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $message->refresh();
        $this->assertEquals('resolved', $message->status);
        $this->assertNotNull($message->resolved_at);
    }

    public function test_admin_can_update_internal_notes(): void
    {
        $message = SchoolMessage::create([
            'school_id' => $this->schoolA->id,
            'reference_number' => 'MSG-NOTES-01',
            'name' => 'Pak Rudi',
            'email' => 'rudi@example.com',
            'category' => 'kerjasama',
            'subject' => 'Kerjasama Kunjungan Edukasi',
            'message' => 'Tawaran kunjungan museum sains.',
            'status' => 'new',
            'is_read' => true,
        ]);

        $response = $this->actingAs($this->adminA)->put(route('website.messages.update-notes', $message), [
            'internal_notes' => 'Telah diteruskan ke Waka Kurikulum, menunggu konfirmasi tanggal 20 Oktober.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $message->refresh();
        $this->assertEquals('Telah diteruskan ke Waka Kurikulum, menunggu konfirmasi tanggal 20 Oktober.', $message->internal_notes);
    }

    public function test_admin_can_delete_message(): void
    {
        $message = SchoolMessage::create([
            'school_id' => $this->schoolA->id,
            'reference_number' => 'MSG-DEL-01',
            'name' => 'Spam Sender',
            'email' => 'spam@test.com',
            'category' => 'lainnya',
            'subject' => 'Spam Subject',
            'message' => 'Spam message to be deleted',
            'status' => 'new',
            'is_read' => true,
        ]);

        $response = $this->actingAs($this->adminA)->delete(route('website.messages.destroy', $message));

        $response->assertRedirect(route('website.messages.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('school_messages', [
            'id' => $message->id,
        ]);
    }

    public function test_admin_can_perform_bulk_actions_on_messages(): void
    {
        $msg1 = SchoolMessage::create([
            'school_id' => $this->schoolA->id,
            'reference_number' => 'MSG-BULK-01',
            'name' => 'User 1',
            'email' => 'u1@example.com',
            'category' => 'akademik',
            'subject' => 'Subject 1',
            'message' => 'Message 1',
            'status' => 'new',
            'is_read' => false,
        ]);

        $msg2 = SchoolMessage::create([
            'school_id' => $this->schoolA->id,
            'reference_number' => 'MSG-BULK-02',
            'name' => 'User 2',
            'email' => 'u2@example.com',
            'category' => 'akademik',
            'subject' => 'Subject 2',
            'message' => 'Message 2',
            'status' => 'new',
            'is_read' => false,
        ]);

        $response = $this->actingAs($this->adminA)->post(route('website.messages.bulk'), [
            'action' => 'mark_resolved',
            'message_ids' => [$msg1->id, $msg2->id],
        ]);

        $response->assertRedirect(route('website.messages.index'));

        $this->assertEquals('resolved', $msg1->fresh()->status);
        $this->assertEquals('resolved', $msg2->fresh()->status);
        $this->assertTrue($msg1->fresh()->is_read);
    }

    // =========================================================================
    // 4. TENANT ISOLATION & AUTHORIZATION — NEGATIVE CASES
    // =========================================================================

    public function test_guest_cannot_access_admin_messages_dashboard(): void
    {
        $response = $this->get(route('website.messages.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_unauthorized_roles_cannot_access_admin_messages_dashboard(): void
    {
        $response = $this->actingAs($this->teacher)->get(route('website.messages.index'));
        $response->assertStatus(403);

        $response = $this->actingAs($this->student)->get(route('website.messages.index'));
        $response->assertStatus(403);
    }

    public function test_tenant_isolation_admin_a_cannot_view_school_b_message(): void
    {
        $messageB = SchoolMessage::create([
            'school_id' => $this->schoolB->id,
            'reference_number' => 'MSG-ISOLATION-B',
            'name' => 'Orang Tua Murid Sekolah B',
            'email' => 'wali@surabaya.sch.id',
            'category' => 'akademik',
            'subject' => 'Data Rahasia Siswa Surabaya',
            'message' => 'Konten privat sekolah B.',
            'status' => 'new',
            'is_read' => false,
        ]);

        $response = $this->actingAs($this->adminA)->get(route('website.messages.show', $messageB));

        $response->assertStatus(403);
    }

    public function test_tenant_isolation_admin_a_cannot_update_status_of_school_b_message(): void
    {
        $messageB = SchoolMessage::create([
            'school_id' => $this->schoolB->id,
            'reference_number' => 'MSG-ISOLATION-B2',
            'name' => 'Pengirim B',
            'email' => 'b@example.com',
            'category' => 'akademik',
            'subject' => 'Subjek B',
            'message' => 'Isi B',
            'status' => 'new',
            'is_read' => false,
        ]);

        $response = $this->actingAs($this->adminA)->put(route('website.messages.update-status', $messageB), [
            'status' => 'resolved',
        ]);

        $response->assertStatus(403);
        $this->assertEquals('new', $messageB->fresh()->status);
    }

    public function test_tenant_isolation_admin_a_cannot_update_notes_of_school_b_message(): void
    {
        $messageB = SchoolMessage::create([
            'school_id' => $this->schoolB->id,
            'reference_number' => 'MSG-ISOLATION-B3',
            'name' => 'Pengirim B',
            'email' => 'b@example.com',
            'category' => 'akademik',
            'subject' => 'Subjek B',
            'message' => 'Isi B',
            'status' => 'new',
            'is_read' => false,
        ]);

        $response = $this->actingAs($this->adminA)->put(route('website.messages.update-notes', $messageB), [
            'internal_notes' => 'Catatan liar dari admin sekolah A',
        ]);

        $response->assertStatus(403);
        $this->assertNull($messageB->fresh()->internal_notes);
    }

    public function test_tenant_isolation_admin_a_cannot_delete_school_b_message(): void
    {
        $messageB = SchoolMessage::create([
            'school_id' => $this->schoolB->id,
            'reference_number' => 'MSG-ISOLATION-B4',
            'name' => 'Pengirim B',
            'email' => 'b@example.com',
            'category' => 'akademik',
            'subject' => 'Subjek B',
            'message' => 'Isi B',
            'status' => 'new',
            'is_read' => false,
        ]);

        $response = $this->actingAs($this->adminA)->delete(route('website.messages.destroy', $messageB));

        $response->assertStatus(403);
        $this->assertDatabaseHas('school_messages', ['id' => $messageB->id]);
    }

    public function test_tenant_isolation_bulk_action_does_not_affect_school_b_messages(): void
    {
        $messageA = SchoolMessage::create([
            'school_id' => $this->schoolA->id,
            'reference_number' => 'MSG-BULK-A',
            'name' => 'User A',
            'email' => 'a@example.com',
            'category' => 'akademik',
            'subject' => 'Subject A',
            'message' => 'Message A',
            'status' => 'new',
            'is_read' => false,
        ]);

        $messageB = SchoolMessage::create([
            'school_id' => $this->schoolB->id,
            'reference_number' => 'MSG-BULK-B',
            'name' => 'User B',
            'email' => 'b@example.com',
            'category' => 'akademik',
            'subject' => 'Subject B',
            'message' => 'Message B',
            'status' => 'new',
            'is_read' => false,
        ]);

        // Admin A tries to pass School B's message in bulk delete
        $response = $this->actingAs($this->adminA)->post(route('website.messages.bulk'), [
            'action' => 'delete',
            'message_ids' => [$messageA->id, $messageB->id],
        ]);

        $response->assertRedirect(route('website.messages.index'));

        // Message A deleted
        $this->assertDatabaseMissing('school_messages', ['id' => $messageA->id]);
        // Message B must still exist intact!
        $this->assertDatabaseHas('school_messages', ['id' => $messageB->id]);
    }

    public function test_sitemap_includes_public_contact_page(): void
    {
        $response = $this->get(route('school.public.sitemap', ['school' => $this->schoolA->slug]));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/xml; charset=utf-8');
        $response->assertSee(route('school.public.contact', ['school' => $this->schoolA->slug]));
    }
}
