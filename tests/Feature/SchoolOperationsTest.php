<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\ConsumableItem;
use App\Models\FacilityReservation;
use App\Models\InventoryCategory;
use App\Models\LibraryBook;
use App\Models\LibraryBookCopy;
use App\Models\LibraryLoan;
use App\Models\School;
use App\Models\SchoolAsset;
use App\Models\SchoolLetter;
use App\Models\SchoolRoom;
use App\Models\StudentProfile;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SchoolOperationsTest extends TestCase
{
    use RefreshDatabase;

    private School $school;

    private School $otherSchool;

    private User $admin;

    private User $staff;

    private User $teacher;

    private User $student;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        Storage::fake('public');

        $this->school = School::factory()->create([
            'name' => 'SMK Eduvia Teknologi',
            'slug' => 'smk-eduvia-teknologi',
        ]);

        $this->otherSchool = School::factory()->create([
            'name' => 'SMP Eduvia Sejahtera',
            'slug' => 'smp-eduvia-sejahtera',
        ]);

        $this->admin = User::factory()->create([
            'school_id' => $this->school->id,
            'role' => UserRole::SCHOOL_ADMIN,
        ]);

        $this->staff = User::factory()->create([
            'school_id' => $this->school->id,
            'role' => UserRole::STAFF,
        ]);

        $this->teacher = User::factory()->create([
            'school_id' => $this->school->id,
            'role' => UserRole::TEACHER,
        ]);

        $this->student = User::factory()->create([
            'school_id' => $this->school->id,
            'role' => UserRole::STUDENT,
        ]);

        StudentProfile::create([
            'user_id' => $this->student->id,
            'school_id' => $this->school->id,
            'nis' => '10293',
            'nisn' => '0098765432',
            'gender' => 'male',
            'birth_place' => 'Bandung',
            'birth_date' => '2008-05-14',
            'parent_name' => 'Bambang Supriyadi',
            'student_status' => 'active',
        ]);
    }

    // =========================================================================
    // 1. OPERATIONS DASHBOARD
    // =========================================================================

    public function test_admin_and_staff_can_view_operational_dashboard(): void
    {
        $response = $this->actingAs($this->admin)->get(route('operations.dashboard'));
        $response->assertStatus(200);
        $response->assertSee('Pusat Operasional Sekolah');
        $response->assertSee('Katalog Buku');
        $response->assertSee('Aset Sarpras');
    }

    // =========================================================================
    // 2. LIBRARY MANAGEMENT
    // =========================================================================

    public function test_staff_can_create_book_and_copies_are_automatically_generated(): void
    {
        $payload = [
            'title' => 'Pemrograman Web dengan Laravel & Vue',
            'author' => 'Tim Pengembang Eduvia',
            'publisher' => 'Informatika Edukasi',
            'publication_year' => 2026,
            'isbn' => '978-602-1234-56-7',
            'call_number' => '005.133 TIM p',
            'category' => 'Pelajaran',
            'language' => 'Indonesia',
            'location_shelf' => 'Rak TIK-01',
            'description' => 'Buku teks praktik kejuruan RPL SMK.',
            'initial_copies' => 3,
            'cover_image' => UploadedFile::fake()->image('cover.jpg'),
            'digital_file' => UploadedFile::fake()->create('ebook_laravel.pdf', 500, 'application/pdf'),
        ];

        $response = $this->actingAs($this->staff)->post(route('operations.library.books.store'), $payload);

        $book = LibraryBook::where('title', 'Pemrograman Web dengan Laravel & Vue')->first();
        $this->assertNotNull($book);
        $this->assertEquals(3, $book->total_copies);
        $this->assertEquals(3, $book->available_copies);
        $this->assertCount(3, $book->copies);

        $response->assertRedirect(route('operations.library.books.show', $book));

        // Test downloading digital resource
        $downloadRes = $this->actingAs($this->student)->get(route('operations.library.books.digital', $book));
        $downloadRes->assertStatus(200);
    }

    public function test_staff_can_borrow_and_return_book_with_overdue_fine_calculation(): void
    {
        $book = LibraryBook::create([
            'school_id' => $this->school->id,
            'title' => 'Fisika Terapan SMK',
            'author' => 'Dr. Suryadi',
            'category' => 'Pelajaran',
            'language' => 'Indonesia',
            'total_copies' => 1,
            'available_copies' => 1,
            'is_active' => true,
        ]);

        $copy = LibraryBookCopy::create([
            'school_id' => $this->school->id,
            'library_book_id' => $book->id,
            'barcode' => 'BOK-0001-01',
            'copy_number' => 1,
            'status' => 'available',
        ]);

        // Borrow book
        $borrowPayload = [
            'library_book_copy_id' => $copy->id,
            'user_id' => $this->student->id,
            'due_date' => Carbon::today()->addDays(7)->toDateString(),
            'notes' => 'Peminjaman untuk praktikum fisika',
        ];

        $borrowRes = $this->actingAs($this->staff)->post(route('operations.library.loans.store'), $borrowPayload);
        $borrowRes->assertRedirect(route('operations.library.loans.index'));

        $copy->refresh();
        $book->refresh();
        $this->assertEquals('borrowed', $copy->status);
        $this->assertEquals(0, $book->available_copies);

        $loan = LibraryLoan::where('library_book_copy_id', $copy->id)->where('status', 'borrowed')->first();
        $this->assertNotNull($loan);

        // Simulate returning overdue by setting due date to past (3 days overdue)
        $loan->update(['due_date' => Carbon::today()->subDays(3)]);

        $returnRes = $this->actingAs($this->staff)->post(route('operations.library.loans.return', $loan), [
            'notes' => 'Buku kembali dalam kondisi bersih',
        ]);
        $returnRes->assertRedirect(route('operations.library.loans.index'));

        $loan->refresh();
        $copy->refresh();
        $book->refresh();

        $this->assertEquals('returned', $loan->status);
        $this->assertEquals(3000, $loan->fine_amount); // 3 days * Rp 1.000 = Rp 3.000
        $this->assertEquals('available', $copy->status);
        $this->assertEquals(1, $book->available_copies);
    }

    // =========================================================================
    // 3. INVENTORY AND ASSET MANAGEMENT
    // =========================================================================

    public function test_staff_can_register_asset_and_track_room_transfer(): void
    {
        $cat = InventoryCategory::create([
            'school_id' => $this->school->id,
            'name' => 'Peralatan Laboratorium Komputer',
            'code' => 'LABKOM',
            'type' => 'asset',
        ]);

        $assetPayload = [
            'inventory_category_id' => $cat->id,
            'name' => 'PC Client Core i5 16GB RAM',
            'brand' => 'Lenovo',
            'model_number' => 'ThinkCentre M70q',
            'serial_number' => 'MJ09X812',
            'room_location' => 'Lab Komputer 1',
            'condition' => 'good',
            'status' => 'in_use',
            'acquisition_date' => '2026-01-10',
            'purchase_cost' => 8500000,
            'responsible_person' => 'Ahmad Fauzi, S.Kom.',
        ];

        $response = $this->actingAs($this->staff)->post(route('operations.inventory.assets.store'), $assetPayload);

        $asset = SchoolAsset::where('serial_number', 'MJ09X812')->first();
        $this->assertNotNull($asset);
        $this->assertStringStartsWith('LABKOM/', $asset->asset_code);
        $this->assertEquals('Lab Komputer 1', $asset->room_location);

        $response->assertRedirect(route('operations.inventory.assets.show', $asset));

        // Test Asset Room Transfer
        $transferRes = $this->actingAs($this->staff)->post(route('operations.inventory.assets.transfer', $asset), [
            'new_location' => 'Lab Multimedia B',
            'notes' => 'Pemindahan untuk kebutuhan ujian sertifikasi',
        ]);

        $asset->refresh();
        $this->assertEquals('Lab Multimedia B', $asset->room_location);
        $this->assertCount(2, $asset->logs); // initial registration + transfer
    }

    public function test_staff_can_manage_consumable_stock_and_adjustments(): void
    {
        $cat = InventoryCategory::create([
            'school_id' => $this->school->id,
            'name' => 'ATK dan Bahan Kantor',
            'code' => 'ATK',
            'type' => 'consumable',
        ]);

        $item = ConsumableItem::create([
            'school_id' => $this->school->id,
            'inventory_category_id' => $cat->id,
            'item_code' => 'ATK-KERTAS-A4',
            'name' => 'Kertas HVS PaperOne 75gr A4',
            'unit' => 'Rim',
            'current_stock' => 20,
            'minimum_stock' => 5,
        ]);

        // Stock Out
        $adjustRes = $this->actingAs($this->staff)->post(route('operations.inventory.consumables.adjust', $item), [
            'type' => 'out',
            'quantity' => 16,
            'purpose' => 'Pengambilan untuk pencetakan naskah Ujian Tengah Semester',
        ]);

        $adjustRes->assertRedirect(route('operations.inventory.consumables.index'));

        $item->refresh();
        $this->assertEquals(4, $item->current_stock);
        $this->assertTrue($item->isLowStock()); // 4 <= 5 minimum stock
    }

    // =========================================================================
    // 4. FACILITIES & CONFLICT DETECTION
    // =========================================================================

    public function test_teacher_can_reserve_facility_and_interval_overlap_prevents_conflicts(): void
    {
        $room = SchoolRoom::create([
            'school_id' => $this->school->id,
            'name' => 'Laboratorium Jaringan & Cisco',
            'code' => 'LAB-TKJ',
            'type' => 'laboratory',
            'capacity' => 36,
            'location' => 'Gedung C Lt. 2',
            'is_active' => true,
        ]);

        $date = Carbon::tomorrow()->toDateString();

        // 1. Initial reservation: 08:00 to 10:00
        $res1Payload = [
            'school_room_id' => $room->id,
            'reservation_date' => $date,
            'start_time' => '08:00',
            'end_time' => '10:00',
            'purpose' => 'Praktikum Routing OSPF Kelas 11',
            'expected_participants' => 30,
        ];

        $res1Response = $this->actingAs($this->teacher)->post(route('operations.facilities.reservations.store'), $res1Payload);
        $res1Response->assertRedirect(route('operations.facilities.reservations.index'));

        $res1 = FacilityReservation::where('school_room_id', $room->id)->first();
        $this->assertNotNull($res1);

        // 2. Overlapping reservation attempt: 09:00 to 11:00 (overlaps with 08:00 - 10:00)
        $res2Payload = [
            'school_room_id' => $room->id,
            'reservation_date' => $date,
            'start_time' => '09:00',
            'end_time' => '11:00',
            'purpose' => 'Uji Coba Mikrotik',
            'expected_participants' => 20,
        ];

        $res2Response = $this->actingAs($this->teacher)->post(route('operations.facilities.reservations.store'), $res2Payload);
        $res2Response->assertSessionHasErrors('start_time'); // conflict detection triggered!

        // 3. Non-overlapping reservation attempt: 10:00 to 12:00 (adjacent/after, should succeed)
        $res3Payload = [
            'school_room_id' => $room->id,
            'reservation_date' => $date,
            'start_time' => '10:00',
            'end_time' => '12:00',
            'purpose' => 'Simulasi Packet Tracer',
            'expected_participants' => 25,
        ];

        $res3Response = $this->actingAs($this->teacher)->post(route('operations.facilities.reservations.store'), $res3Payload);
        $res3Response->assertRedirect(route('operations.facilities.reservations.index'));
        $this->assertEquals(2, FacilityReservation::where('school_room_id', $room->id)->count());
    }

    // =========================================================================
    // 5. CORRESPONDENCE & OFFICIAL PDF GENERATION
    // =========================================================================

    public function test_staff_can_record_letter_and_generate_student_certificate_pdf(): void
    {
        // 1. Record incoming letter with attachment
        $letterPayload = [
            'type' => 'incoming',
            'reference_number' => '421.3/981/Disdik/2026',
            'letter_date' => '2026-10-10',
            'received_or_sent_date' => '2026-10-12',
            'sender_or_recipient' => 'Dinas Pendidikan Provinsi DKI Jakarta',
            'subject' => 'Instruksi Pelaksanaan Asesmen Nasional Berbasis Komputer',
            'category' => 'Dinas',
            'status' => 'action_needed',
            'attachment' => UploadedFile::fake()->create('surat_disdik.pdf', 300, 'application/pdf'),
        ];

        $response = $this->actingAs($this->staff)->post(route('operations.correspondence.store'), $letterPayload);
        $letter = SchoolLetter::where('reference_number', '421.3/981/Disdik/2026')->first();
        $this->assertNotNull($letter);
        $this->assertNotNull($letter->file_path);

        $response->assertRedirect(route('operations.correspondence.show', $letter));

        // 2. Generate Surat Keterangan Siswa Aktif PDF
        $certPayload = [
            'student_id' => $this->student->id,
            'purpose' => 'Persyaratan Pembuatan Paspor Kunjungan Edukasi Industri',
        ];

        $pdfResponse = $this->actingAs($this->staff)->post(route('operations.correspondence.certificate.pdf'), $certPayload);
        $pdfResponse->assertStatus(200);
        $this->assertEquals('application/pdf', $pdfResponse->headers->get('Content-Type'));

        // Check that an outgoing letter record was automatically created in the agenda
        $this->assertDatabaseHas('school_letters', [
            'school_id' => $this->school->id,
            'type' => 'outgoing',
            'sender_or_recipient' => $this->student->name,
            'category' => 'Keterangan',
        ]);
    }

    // =========================================================================
    // 6. MULTI-TENANCY & IDOR PREVENTION
    // =========================================================================

    public function test_cannot_access_or_modify_other_school_operational_assets_and_books(): void
    {
        // Create asset and book in other school
        $otherCat = InventoryCategory::create([
            'school_id' => $this->otherSchool->id,
            'name' => 'Perabot Sekolah Lain',
            'code' => 'MEB',
            'type' => 'asset',
        ]);

        $otherAsset = SchoolAsset::create([
            'school_id' => $this->otherSchool->id,
            'inventory_category_id' => $otherCat->id,
            'asset_code' => 'MEB/2026/0099',
            'name' => 'Meja Kepala Sekolah',
            'room_location' => 'Ruang Kepala Sekolah Lain',
            'condition' => 'good',
            'status' => 'in_use',
        ]);

        $otherBook = LibraryBook::create([
            'school_id' => $this->otherSchool->id,
            'title' => 'Buku Rahasia Sekolah Sejahtera',
            'author' => 'Anonim',
            'category' => 'Referensi',
            'language' => 'Indonesia',
            'total_copies' => 1,
            'available_copies' => 1,
            'is_active' => true,
        ]);

        // Attempt accessing from our school's staff
        $assetRes = $this->actingAs($this->staff)->get(route('operations.inventory.assets.show', $otherAsset));
        $this->assertTrue(in_array($assetRes->status(), [403, 404]), "Expected 403 or 404 but got {$assetRes->status()}");

        $bookRes = $this->actingAs($this->staff)->get(route('operations.library.books.show', $otherBook));
        $this->assertTrue(in_array($bookRes->status(), [403, 404]), "Expected 403 or 404 but got {$bookRes->status()}");
    }
}
