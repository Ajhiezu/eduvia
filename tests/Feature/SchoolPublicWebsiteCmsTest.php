<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\AcademicYear;
use App\Models\AdmissionPeriod;
use App\Models\AdmissionTrack;
use App\Models\School;
use App\Models\SchoolExtracurricular;
use App\Models\SchoolFacility;
use App\Models\SchoolPost;
use App\Models\SchoolWebsiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SchoolPublicWebsiteCmsTest extends TestCase
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

        Storage::fake('public');

        $this->schoolA = School::factory()->create([
            'name' => 'SMA Eduvia Jakarta',
            'slug' => 'eduvia-jakarta',
            'public_website_enabled' => true,
        ]);

        $this->schoolB = School::factory()->create([
            'name' => 'SMP Eduvia Surabaya',
            'slug' => 'eduvia-surabaya',
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
            'school_id' => $this->schoolA->id,
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

    public function test_public_visitor_can_access_active_school_landing_page(): void
    {
        $response = $this->get(route('school.public.home', ['school' => $this->schoolA->slug]));

        $response->assertStatus(200);
        $response->assertSee('SMA Eduvia Jakarta');
        $response->assertSee('Situs Resmi');
    }

    public function test_public_visitor_cannot_access_disabled_school_website(): void
    {
        $this->schoolA->update(['public_website_enabled' => false]);

        $response = $this->get(route('school.public.home', ['school' => $this->schoolA->slug]));

        $response->assertStatus(404);
    }

    public function test_school_admin_can_preview_disabled_school_website(): void
    {
        $this->schoolA->update(['public_website_enabled' => false]);

        $response = $this->actingAs($this->adminA)
            ->get(route('school.public.home', ['school' => $this->schoolA->slug]));

        $response->assertStatus(200);
        $response->assertSee('MODE PRATINJAU DRAF');
    }

    public function test_public_landing_page_displays_real_published_content(): void
    {
        SchoolWebsiteSetting::create([
            'school_id' => $this->schoolA->id,
            'hero_headline' => 'Pusat Keunggulan Sains & Karakter',
            'welcome_message' => 'Selamat datang di kampus kami yang asri dan berprestasi.',
            'principal_name' => 'Dr. Suryo Pranoto, M.Pd.',
            'accreditation_grade' => 'A (Unggul)',
            'is_published' => true,
        ]);

        $publishedPost = SchoolPost::create([
            'school_id' => $this->schoolA->id,
            'type' => 'news',
            'title' => 'Juara Debat Bahasa Inggris Tingkat Nasional',
            'slug' => 'juara-debat-bahasa-inggris-nasional',
            'content' => 'Siswa kami berhasil meraih podium juara satu.',
            'is_published' => true,
            'published_at' => now(),
        ]);

        $draftPost = SchoolPost::create([
            'school_id' => $this->schoolA->id,
            'type' => 'news',
            'title' => 'Rencana Ujian Internal (Rahasia)',
            'slug' => 'rencana-ujian-internal-rahasia',
            'content' => 'Konten rahasia draf kurikulum.',
            'is_published' => false,
        ]);

        $response = $this->get(route('school.public.home', ['school' => $this->schoolA->slug]));

        $response->assertStatus(200);
        $response->assertSee('Pusat Keunggulan Sains & Karakter');
        $response->assertSee('Dr. Suryo Pranoto, M.Pd.');
        $response->assertSee('Juara Debat Bahasa Inggris Tingkat Nasional');
        $response->assertDontSee('Rencana Ujian Internal (Rahasia)');
    }

    public function test_draft_article_detail_is_protected_from_public_visitors(): void
    {
        $draftPost = SchoolPost::create([
            'school_id' => $this->schoolA->id,
            'type' => 'news',
            'title' => 'Draf Berita Rahasia',
            'slug' => 'draf-berita-rahasia',
            'content' => 'Konten draf yang belum disetujui.',
            'is_published' => false,
        ]);

        // Anonymous public visitor gets 404
        $this->get(route('school.public.article-detail', ['school' => $this->schoolA->slug, 'slug' => $draftPost->slug]))
            ->assertStatus(404);

        // School Admin can view it with preview indicator
        $this->actingAs($this->adminA)
            ->get(route('school.public.article-detail', ['school' => $this->schoolA->slug, 'slug' => $draftPost->slug]))
            ->assertStatus(200)
            ->assertSee('Draf Internal (Pratinjau)')
            ->assertSee('Draf Berita Rahasia');
    }

    public function test_unpublish_post_removes_content_from_public(): void
    {
        $post = SchoolPost::create([
            'school_id' => $this->schoolA->id,
            'type' => 'news',
            'title' => 'Berita Yang Akan Ditarik',
            'slug' => 'berita-yang-akan-ditarik',
            'content' => 'Konten artikel.',
            'is_published' => true,
            'published_at' => now(),
        ]);

        // Visible when published
        $this->get(route('school.public.articles', ['school' => $this->schoolA->slug]))
            ->assertSee('Berita Yang Akan Ditarik');

        // Unpublish via CMS action
        $this->actingAs($this->adminA)
            ->post(route('website.posts.toggle-publish', $post));

        $this->assertFalse($post->fresh()->is_published);

        // No longer visible to public
        $this->get(route('school.public.articles', ['school' => $this->schoolA->slug]))
            ->assertDontSee('Berita Yang Akan Ditarik');
    }

    public function test_school_admin_can_access_cms_dashboard(): void
    {
        $response = $this->actingAs($this->adminA)
            ->get(route('website.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('CMS Website Publik');
        $response->assertSee('Pengelolaan Website Sekolah: SMA Eduvia Jakarta');
        $response->assertSee('Alamat URL Publik Sekolah');
    }

    public function test_teacher_student_and_guest_cannot_access_cms(): void
    {
        $this->get(route('website.dashboard'))->assertRedirect(route('login'));
        $this->actingAs($this->teacher)->get(route('website.dashboard'))->assertStatus(403);
        $this->actingAs($this->student)->get(route('website.dashboard'))->assertStatus(403);
    }

    public function test_school_admin_can_update_website_settings_and_slug(): void
    {
        $heroImage = UploadedFile::fake()->image('hero-banner.jpg', 1200, 600);
        $principalPhoto = UploadedFile::fake()->image('principal.jpg', 400, 500);

        $response = $this->actingAs($this->adminA)->put(route('website.settings.update'), [
            'slug' => 'sma-eduvia-jakarta-pusat',
            'public_website_enabled' => 1,
            'hero_headline' => 'Sekolah Generasi Emas Masa Depan',
            'hero_subheadline' => 'Mewujudkan kepemimpinan berakhlak mulia.',
            'hero_cta_text' => 'Daftar Sekarang',
            'hero_cta_url' => '#ppdb',
            'welcome_message' => 'Selamat datang di sekolah kami tercinta.',
            'principal_name' => 'Drs. Bambang Sudirman, M.Pd.',
            'principal_title' => 'Kepala SMA Eduvia Jakarta',
            'vision' => 'Menjadi SMA unggulan bertaraf internasional.',
            'mission' => "Mendidik dengan hati\nMenumbuhkan inovasi sains\nMembentuk budi pekerti",
            'history' => 'Berdiri sejak tahun 2005 di kawasan Jakarta.',
            'accreditation_grade' => 'A (Unggul)',
            'accreditation_no' => 'SK/BAN-SM/2024',
            'facebook_url' => 'https://facebook.com/eduvia',
            'instagram_url' => 'https://instagram.com/eduvia',
            'meta_description' => 'Situs resmi SMA Eduvia Jakarta Pusat.',
            'meta_keywords' => 'SMA Eduvia, SMA Terbaik Jakarta',
            'hero_image' => $heroImage,
            'principal_photo' => $principalPhoto,
        ]);

        $response->assertRedirect(route('website.settings'));
        $this->assertDatabaseHas('schools', [
            'id' => $this->schoolA->id,
            'slug' => 'sma-eduvia-jakarta-pusat',
            'public_website_enabled' => true,
        ]);

        $setting = SchoolWebsiteSetting::where('school_id', $this->schoolA->id)->first();
        $this->assertNotNull($setting);
        $this->assertEquals('Sekolah Generasi Emas Masa Depan', $setting->hero_headline);
        $this->assertCount(3, $setting->mission);
        $this->assertNotNull($setting->hero_image_path);
        $this->assertNotNull($setting->principal_photo_path);

        Storage::disk('public')->assertExists($setting->hero_image_path);
        Storage::disk('public')->assertExists($setting->principal_photo_path);
    }

    public function test_slug_duplicate_is_rejected(): void
    {
        $response = $this->actingAs($this->adminA)->put(route('website.settings.update'), [
            'slug' => $this->schoolB->slug, // already used by schoolB
            'public_website_enabled' => 1,
        ]);

        $response->assertSessionHasErrors(['slug']);
    }

    public function test_school_admin_can_create_and_publish_news_post(): void
    {
        $cover = UploadedFile::fake()->image('news-cover.jpg', 800, 600);

        $response = $this->actingAs($this->adminA)->post(route('website.posts.store'), [
            'type' => 'news',
            'title' => 'Inovasi Panel Surya Buatan Siswa Kelas XII',
            'category' => 'Sains & Lingkungan',
            'excerpt' => 'Siswa berhasil membuat panel surya hemat biaya.',
            'content' => 'Proyek penelitian ilmiah ini dikerjakan selama 3 bulan.',
            'is_published' => 1,
            'cover_image' => $cover,
        ]);

        $response->assertRedirect(route('website.posts.index', ['type' => 'news']));

        $post = SchoolPost::where('school_id', $this->schoolA->id)->where('type', 'news')->first();
        $this->assertNotNull($post);
        $this->assertEquals('Inovasi Panel Surya Buatan Siswa Kelas XII', $post->title);
        $this->assertEquals('inovasi-panel-surya-buatan-siswa-kelas-xii', $post->slug);
        $this->assertTrue($post->is_published);
        $this->assertNotNull($post->published_at);
        $this->assertNotNull($post->cover_image_path);

        Storage::disk('public')->assertExists($post->cover_image_path);
    }

    public function test_school_admin_can_add_events_and_extracurriculars(): void
    {
        // Event
        $this->actingAs($this->adminA)->post(route('website.posts.store'), [
            'type' => 'event',
            'title' => 'Pekan Olahraga Antar Kelas (Porseni)',
            'category' => 'Olahraga',
            'content' => 'Kompetisi futsal dan basket antar kelas.',
            'event_date' => now()->addDays(10)->format('Y-m-d H:i:s'),
            'event_location' => 'Sport Hall Kampus A',
            'is_published' => 1,
        ])->assertRedirect();

        $this->assertDatabaseHas('school_posts', [
            'school_id' => $this->schoolA->id,
            'type' => 'event',
            'title' => 'Pekan Olahraga Antar Kelas (Porseni)',
            'event_location' => 'Sport Hall Kampus A',
        ]);

        // Extracurricular
        $this->actingAs($this->adminA)->post(route('website.extracurriculars.store'), [
            'name' => 'Klub Coding & Robotika',
            'category' => 'Teknologi',
            'description' => 'Belajar Arduino dan Python.',
            'coach_name' => 'Andi Wijaya, S.Kom',
            'schedule_info' => 'Setiap Rabu 15:30',
            'is_published' => 1,
        ])->assertRedirect(route('website.extracurriculars.index'));

        $this->assertDatabaseHas('school_extracurriculars', [
            'school_id' => $this->schoolA->id,
            'name' => 'Klub Coding & Robotika',
            'slug' => 'klub-coding-robotika',
        ]);
    }

    public function test_school_admin_can_add_facilities_and_galleries(): void
    {
        // Facility
        $this->actingAs($this->adminA)->post(route('website.facilities.store'), [
            'name' => 'Laboratorium Biologi Terpadu',
            'category' => 'facility',
            'description' => 'Lengkap dengan mikroskop elektron dan ruang steril.',
            'is_published' => 1,
        ])->assertRedirect(route('website.facilities.index'));

        $this->assertDatabaseHas('school_facilities', [
            'school_id' => $this->schoolA->id,
            'name' => 'Laboratorium Biologi Terpadu',
            'category' => 'facility',
        ]);

        // Gallery
        $photo = UploadedFile::fake()->image('lab-biologi.jpg', 800, 600);
        $this->actingAs($this->adminA)->post(route('website.galleries.store'), [
            'album_name' => 'Praktikum Laboratorium',
            'title' => 'Siswa meneliti sel tumbuhan',
            'caption' => 'Praktikum rutin biologi semester ganjil',
            'media_file' => $photo,
        ])->assertRedirect(route('website.galleries.index'));

        $this->assertDatabaseHas('school_galleries', [
            'school_id' => $this->schoolA->id,
            'album_name' => 'Praktikum Laboratorium',
            'title' => 'Siswa meneliti sel tumbuhan',
        ]);
    }

    public function test_invalid_upload_file_is_rejected(): void
    {
        $fakeScript = UploadedFile::fake()->create('malicious.php', 500, 'text/x-php');

        $response = $this->actingAs($this->adminA)->post(route('website.galleries.store'), [
            'album_name' => 'Test Album',
            'title' => 'File Jahat',
            'media_file' => $fakeScript,
        ]);

        $response->assertSessionHasErrors(['media_file']);
    }

    public function test_strict_multi_tenant_isolation_prevents_cross_school_tampering(): void
    {
        $postB = SchoolPost::create([
            'school_id' => $this->schoolB->id,
            'type' => 'news',
            'title' => 'Berita Milik Sekolah B',
            'slug' => 'berita-milik-sekolah-b',
            'content' => 'Konten rahasia Sekolah B.',
            'is_published' => true,
        ]);

        $extraB = SchoolExtracurricular::create([
            'school_id' => $this->schoolB->id,
            'name' => 'Ekstrakurikuler Sekolah B',
            'slug' => 'ekstrakurikuler-sekolah-b',
            'category' => 'Seni',
            'is_published' => true,
        ]);

        // Admin A tries to edit Post of School B -> 403 Forbidden or 404 Not Found (Scoped)
        $resEdit = $this->actingAs($this->adminA)
            ->get(route('website.posts.edit', $postB));
        $this->assertTrue(in_array($resEdit->status(), [403, 404], true));

        // Admin A tries to update Post of School B -> 403 Forbidden or 404 Not Found (Scoped)
        $resUpdate = $this->actingAs($this->adminA)
            ->put(route('website.posts.update', $postB), [
                'type' => 'news',
                'title' => 'Upaya Pembajakan Konten',
                'content' => 'Mencoba menimpa berita Sekolah B.',
                'is_published' => 1,
            ]);
        $this->assertTrue(in_array($resUpdate->status(), [403, 404], true));

        // Admin A tries to delete Post of School B -> 403 Forbidden or 404 Not Found (Scoped)
        $resDelete = $this->actingAs($this->adminA)
            ->delete(route('website.posts.destroy', $postB));
        $this->assertTrue(in_array($resDelete->status(), [403, 404], true));

        $this->assertDatabaseHas('school_posts', [
            'id' => $postB->id,
            'title' => 'Berita Milik Sekolah B',
        ]);

        // Admin A tries to delete Extracurricular of School B -> 403 Forbidden or 404 Not Found (Scoped)
        $resDeleteExtra = $this->actingAs($this->adminA)
            ->delete(route('website.extracurriculars.destroy', $extraB));
        $this->assertTrue(in_array($resDeleteExtra->status(), [403, 404], true));

        $this->assertDatabaseHas('school_extracurriculars', [
            'id' => $extraB->id,
            'name' => 'Ekstrakurikuler Sekolah B',
        ]);
    }

    public function test_public_website_integrates_active_ppdb_admission_period(): void
    {
        $academicYear = AcademicYear::factory()->create(['school_id' => $this->schoolA->id]);

        $period = AdmissionPeriod::create([
            'school_id' => $this->schoolA->id,
            'academic_year_id' => $academicYear->id,
            'name' => 'PPDB Gelombang 1 Tahun Ajaran 2026/2027',
            'code' => 'PPDB2026G1',
            'start_date' => now()->subDays(5)->toDateString(),
            'end_date' => now()->addDays(20)->toDateString(),
            'announcement_date' => now()->addDays(25)->toDateString(),
            're_registration_start_date' => now()->addDays(26)->toDateString(),
            're_registration_end_date' => now()->addDays(30)->toDateString(),
            'quota' => 100,
            'status' => 'open',
            'is_active' => true,
        ]);

        AdmissionTrack::create([
            'admission_period_id' => $period->id,
            'name' => 'Jalur Prestasi Akademik',
            'code' => 'PRESTASI',
            'quota' => 50,
            'is_active' => true,
        ]);

        $response = $this->get(route('school.public.home', ['school' => $this->schoolA->slug]));

        $response->assertStatus(200);
        $response->assertSee('PPDB Gelombang 1 Tahun Ajaran 2026/2027');
        $response->assertSee('Jalur Prestasi Akademik');
        $response->assertSee(route('ppdb.register', ['period' => $period->code]));
    }

    public function test_public_events_distinguishes_upcoming_and_past_events(): void
    {
        $upcoming = SchoolPost::create([
            'school_id' => $this->schoolA->id,
            'type' => 'event',
            'title' => 'Seminar Kecerdasan Buatan Siswa',
            'slug' => 'seminar-kecerdasan-buatan-siswa',
            'content' => 'Acara seminar mendatang.',
            'event_date' => now()->addDays(14),
            'is_published' => true,
        ]);

        $past = SchoolPost::create([
            'school_id' => $this->schoolA->id,
            'type' => 'event',
            'title' => 'Peringatan Hari Guru Lampau',
            'slug' => 'peringatan-hari-guru-lampau',
            'content' => 'Dokumentasi acara yang telah berlalu.',
            'event_date' => now()->subDays(30),
            'is_published' => true,
        ]);

        $response = $this->get(route('school.public.events', ['school' => $this->schoolA->slug]));

        $response->assertStatus(200);
        $response->assertSee('Agenda Acara Mendatang');
        $response->assertSee('Seminar Kecerdasan Buatan Siswa');
        $response->assertSee('Dokumentasi Acara Selesai');
        $response->assertSee('Peringatan Hari Guru Lampau');
    }

    public function test_all_public_pages_render_successfully(): void
    {
        $routes = [
            route('school.public.home', ['school' => $this->schoolA->slug]),
            route('school.public.profile', ['school' => $this->schoolA->slug]),
            route('school.public.articles', ['school' => $this->schoolA->slug]),
            route('school.public.events', ['school' => $this->schoolA->slug]),
            route('school.public.achievements', ['school' => $this->schoolA->slug]),
            route('school.public.extracurriculars', ['school' => $this->schoolA->slug]),
            route('school.public.facilities', ['school' => $this->schoolA->slug]),
            route('school.public.gallery', ['school' => $this->schoolA->slug]),
            route('school.public.sitemap', ['school' => $this->schoolA->slug]),
        ];

        foreach ($routes as $url) {
            $this->get($url)->assertStatus(200);
        }
    }

    public function test_missing_image_file_returns_null_and_displays_placeholder(): void
    {
        $post = SchoolPost::create([
            'school_id' => $this->schoolA->id,
            'type' => 'news',
            'title' => 'Berita Gambar Hilang',
            'slug' => 'berita-gambar-hilang',
            'content' => 'Konten berita.',
            'cover_image_path' => 'schools/99/nonexistent-image.jpg',
            'is_published' => true,
            'published_at' => now(),
        ]);

        $this->assertNull($post->coverImageUrl());

        $response = $this->get(route('school.public.articles', ['school' => $this->schoolA->slug]));
        $response->assertStatus(200);
        $response->assertSee('Berita Gambar Hilang');
        $response->assertDontSee('nonexistent-image.jpg');
    }

    public function test_admin_can_remove_post_cover_image_without_reuploading(): void
    {
        $file = UploadedFile::fake()->image('post-cover.jpg');
        $path = $file->store('schools/'.$this->schoolA->id.'/posts', 'public');

        $post = SchoolPost::create([
            'school_id' => $this->schoolA->id,
            'type' => 'news',
            'title' => 'Berita Dengan Sampul',
            'slug' => 'berita-dengan-sampul',
            'content' => 'Konten berita.',
            'cover_image_path' => $path,
            'is_published' => true,
        ]);

        $this->assertTrue(Storage::disk('public')->exists($path));

        $response = $this->actingAs($this->adminA)->put(route('website.posts.update', $post), [
            'type' => 'news',
            'title' => 'Berita Dengan Sampul (Diubah)',
            'content' => 'Konten berita yang diperbarui.',
            'is_published' => 1,
            'remove_cover_image' => 1,
        ]);

        $response->assertRedirect(route('website.posts.index', ['type' => 'news']));
        $post->refresh();
        $this->assertNull($post->cover_image_path);
        $this->assertFalse(Storage::disk('public')->exists($path));
    }

    public function test_admin_can_remove_facility_cover_image_without_reuploading(): void
    {
        $file = UploadedFile::fake()->image('facility.jpg');
        $path = $file->store('schools/'.$this->schoolA->id.'/facilities', 'public');

        $facility = SchoolFacility::create([
            'school_id' => $this->schoolA->id,
            'name' => 'Lab Komputer AI',
            'category' => 'facility',
            'cover_image_path' => $path,
            'is_published' => true,
        ]);

        $this->actingAs($this->adminA)->put(route('website.facilities.update', $facility), [
            'name' => 'Lab Komputer AI Updated',
            'category' => 'facility',
            'is_published' => 1,
            'remove_cover_image' => 1,
        ]);

        $facility->refresh();
        $this->assertNull($facility->cover_image_path);
        $this->assertFalse(Storage::disk('public')->exists($path));
    }

    public function test_admin_can_remove_settings_hero_and_principal_photos(): void
    {
        $heroFile = UploadedFile::fake()->image('hero.jpg');
        $heroPath = $heroFile->store('schools/'.$this->schoolA->id.'/website', 'public');
        $photoFile = UploadedFile::fake()->image('photo.jpg');
        $photoPath = $photoFile->store('schools/'.$this->schoolA->id.'/website', 'public');

        $setting = SchoolWebsiteSetting::create([
            'school_id' => $this->schoolA->id,
            'hero_image_path' => $heroPath,
            'principal_photo_path' => $photoPath,
            'is_published' => true,
        ]);

        $this->actingAs($this->adminA)->put(route('website.settings.update'), [
            'slug' => $this->schoolA->slug,
            'public_website_enabled' => 1,
            'remove_hero_image' => 1,
            'remove_principal_photo' => 1,
        ]);

        $setting->refresh();
        $this->assertNull($setting->hero_image_path);
        $this->assertNull($setting->principal_photo_path);
        $this->assertFalse(Storage::disk('public')->exists($heroPath));
        $this->assertFalse(Storage::disk('public')->exists($photoPath));
    }

    public function test_content_ordering_respects_order_index(): void
    {
        $extra2 = SchoolExtracurricular::create([
            'school_id' => $this->schoolA->id,
            'name' => 'Robotika',
            'slug' => 'robotika',
            'category' => 'Teknologi',
            'order_index' => 10,
            'is_published' => true,
        ]);

        $extra1 = SchoolExtracurricular::create([
            'school_id' => $this->schoolA->id,
            'name' => 'Pramuka Garuda',
            'slug' => 'pramuka-garuda',
            'category' => 'Kepanduan',
            'order_index' => 1,
            'is_published' => true,
        ]);

        $response = $this->get(route('school.public.extracurriculars', ['school' => $this->schoolA->slug]));
        $response->assertStatus(200);

        // Extra1 (order_index 1) must appear before Extra2 (order_index 10)
        $response->assertSeeInOrder(['Pramuka Garuda', 'Robotika']);
    }

    public function test_theme_color_validation_and_dynamic_rendering(): void
    {
        // Valid custom hex color
        $this->actingAs($this->adminA)->put(route('website.settings.update'), [
            'slug' => $this->schoolA->slug,
            'public_website_enabled' => 1,
            'theme_color' => '#1D4ED8',
        ]);

        $setting = $this->schoolA->websiteSetting()->first();
        $this->assertEquals('#1D4ED8', $setting->theme_color);

        $response = $this->get(route('school.public.home', ['school' => $this->schoolA->slug]));
        $response->assertStatus(200);
        $response->assertSee('--brand-primary: #1D4ED8');

        // Invalid arbitrary string is rejected by validator
        $invalidResponse = $this->actingAs($this->adminA)->put(route('website.settings.update'), [
            'slug' => $this->schoolA->slug,
            'public_website_enabled' => 1,
            'theme_color' => 'javascript:alert(1)',
        ]);
        $invalidResponse->assertSessionHasErrors(['theme_color']);
    }

    public function test_sitemap_xml_generates_valid_response_with_only_published_content(): void
    {
        $publishedPost = SchoolPost::create([
            'school_id' => $this->schoolA->id,
            'type' => 'news',
            'title' => 'Berita Publik Untuk Sitemap',
            'slug' => 'berita-publik-untuk-sitemap',
            'content' => 'Konten.',
            'is_published' => true,
            'published_at' => now(),
        ]);

        $draftPost = SchoolPost::create([
            'school_id' => $this->schoolA->id,
            'type' => 'news',
            'title' => 'Draft Rahasia Tidak Ada di Sitemap',
            'slug' => 'draft-rahasia-tidak-ada-di-sitemap',
            'content' => 'Konten.',
            'is_published' => false,
        ]);

        $otherSchoolPost = SchoolPost::create([
            'school_id' => $this->schoolB->id,
            'type' => 'news',
            'title' => 'Berita Sekolah Lain',
            'slug' => 'berita-sekolah-lain',
            'content' => 'Konten sekolah B.',
            'is_published' => true,
            'published_at' => now(),
        ]);

        $response = $this->get(route('school.public.sitemap', ['school' => $this->schoolA->slug]));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/xml; charset=utf-8');
        $response->assertSee('<urlset', false);
        $response->assertSee(route('school.public.home', ['school' => $this->schoolA->slug]));
        $response->assertSee(route('school.public.article-detail', ['school' => $this->schoolA->slug, 'slug' => $publishedPost->slug]));
        $response->assertDontSee($draftPost->slug);
        $response->assertDontSee($otherSchoolPost->slug);
    }

    public function test_sitemap_returns_404_when_school_website_disabled(): void
    {
        $this->schoolA->update(['public_website_enabled' => false]);

        $response = $this->get(route('school.public.sitemap', ['school' => $this->schoolA->slug]));

        $response->assertStatus(404);
    }
}
