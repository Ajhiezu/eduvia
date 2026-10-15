<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // =========================================================================
        // 1. MODULE B: SCHOOL LIBRARY MANAGEMENT
        // =========================================================================

        Schema::create('library_books', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('title', 200);
            $table->string('author', 150);
            $table->string('publisher', 150)->nullable();
            $table->unsignedSmallInteger('publication_year')->nullable();
            $table->string('isbn', 30)->nullable();
            $table->string('call_number', 50)->nullable(); // Klasifikasi DDC
            $table->string('category', 50)->default('Pelajaran'); // Pelajaran, Referensi, Fiksi, Non-Fiksi, Kamus, Majalah/Jurnal
            $table->string('language', 30)->default('Indonesia');
            $table->string('location_shelf', 100)->nullable();
            $table->text('description')->nullable();
            $table->string('cover_image_path', 255)->nullable();
            $table->string('digital_file_path', 255)->nullable(); // E-Book PDF
            $table->unsignedSmallInteger('total_copies')->default(1);
            $table->unsignedSmallInteger('available_copies')->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['school_id', 'title']);
            $table->index(['school_id', 'category']);
            $table->index(['school_id', 'isbn']);
        });

        Schema::create('library_book_copies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('library_book_id')->constrained('library_books')->cascadeOnDelete();
            $table->string('barcode', 50);
            $table->unsignedSmallInteger('copy_number')->default(1);
            $table->string('status', 30)->default('available'); // available, borrowed, damaged, lost, maintenance
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['school_id', 'barcode']);
            $table->index(['school_id', 'status']);
        });

        Schema::create('library_loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('library_book_copy_id')->constrained('library_book_copies')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('loan_code', 50)->unique();
            $table->date('borrow_date');
            $table->date('due_date');
            $table->date('return_date')->nullable();
            $table->string('status', 30)->default('borrowed'); // borrowed, returned, overdue, lost
            $table->unsignedBigInteger('fine_amount')->default(0); // Integer Rupiah
            $table->boolean('fine_paid')->default(false);
            $table->text('notes')->nullable();
            $table->foreignId('issued_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['school_id', 'status']);
            $table->index(['user_id', 'status']);
            $table->index(['school_id', 'due_date']);
        });

        // =========================================================================
        // 2. MODULE C: INVENTORY AND ASSET MANAGEMENT
        // =========================================================================

        Schema::create('inventory_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('name', 100);
            $table->string('code', 30);
            $table->string('type', 20)->default('asset'); // asset, consumable
            $table->timestamps();

            $table->unique(['school_id', 'code']);
        });

        Schema::create('school_assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('inventory_category_id')->constrained('inventory_categories')->cascadeOnDelete();
            $table->string('asset_code', 50)->unique();
            $table->string('name', 150);
            $table->string('brand', 100)->nullable();
            $table->string('model_number', 100)->nullable();
            $table->string('serial_number', 100)->nullable();
            $table->string('room_location', 150);
            $table->string('condition', 30)->default('good'); // good, minor_damage, heavy_damage, disposed
            $table->string('status', 30)->default('in_use'); // in_use, in_storage, maintenance, disposed
            $table->date('acquisition_date')->nullable();
            $table->unsignedBigInteger('purchase_cost')->default(0); // Integer Rupiah
            $table->string('responsible_person', 150)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'condition']);
            $table->index(['school_id', 'room_location']);
            $table->index(['school_id', 'status']);
        });

        Schema::create('asset_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('school_asset_id')->constrained('school_assets')->cascadeOnDelete();
            $table->string('action', 50); // registered, transferred, condition_change, maintenance, disposed
            $table->string('from_location', 150)->nullable();
            $table->string('to_location', 150)->nullable();
            $table->string('from_condition', 50)->nullable();
            $table->string('to_condition', 50)->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('performed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['school_asset_id', 'created_at']);
        });

        Schema::create('consumable_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('inventory_category_id')->constrained('inventory_categories')->cascadeOnDelete();
            $table->string('item_code', 50);
            $table->string('name', 150);
            $table->string('unit', 30)->default('Pcs');
            $table->integer('current_stock')->default(0);
            $table->integer('minimum_stock')->default(5);
            $table->timestamps();

            $table->unique(['school_id', 'item_code']);
        });

        Schema::create('consumable_stock_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('consumable_item_id')->constrained('consumable_items')->cascadeOnDelete();
            $table->string('type', 20); // in, out, adjustment
            $table->integer('quantity');
            $table->integer('previous_stock');
            $table->integer('current_stock');
            $table->string('purpose', 255)->nullable();
            $table->foreignId('performed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // =========================================================================
        // 3. MODULE D: LABORATORIES AND SCHOOL FACILITIES
        // =========================================================================

        Schema::create('school_rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('name', 150);
            $table->string('code', 50);
            $table->string('type', 50)->default('laboratory'); // laboratory, classroom, hall, sports_field, meeting_room, workshop, library_room
            $table->unsignedSmallInteger('capacity')->default(30);
            $table->string('location', 150)->nullable();
            $table->string('manager_name', 150)->nullable();
            $table->text('facilities_description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['school_id', 'code']);
        });

        Schema::create('facility_reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('school_room_id')->constrained('school_rooms')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->date('reservation_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->string('purpose', 255);
            $table->unsignedSmallInteger('expected_participants')->default(1);
            $table->string('status', 30)->default('pending'); // pending, approved, rejected, cancelled, completed
            $table->text('rejection_reason')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('approved_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'reservation_date']);
            $table->index(['school_room_id', 'reservation_date', 'status'], 'fac_res_room_date_status_idx');
        });

        Schema::create('facility_incidents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('school_room_id')->constrained('school_rooms')->cascadeOnDelete();
            $table->foreignId('reported_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title', 200);
            $table->text('description');
            $table->string('priority', 20)->default('medium'); // low, medium, high, urgent
            $table->string('status', 30)->default('reported'); // reported, in_progress, resolved, closed
            $table->text('resolution_notes')->nullable();
            $table->dateTime('resolved_at')->nullable();
            $table->timestamps();

            $table->index(['school_id', 'status']);
        });

        // =========================================================================
        // 4. MODULE E: SCHOOL CORRESPONDENCE AND DOCUMENT MANAGEMENT
        // =========================================================================

        Schema::create('school_letters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('type', 20)->default('incoming'); // incoming, outgoing
            $table->string('reference_number', 100);
            $table->date('letter_date');
            $table->date('received_or_sent_date');
            $table->string('sender_or_recipient', 200);
            $table->string('subject', 255);
            $table->string('category', 50)->default('Dinas'); // Dinas, Undangan, Edaran, Surat Tugas, Keterangan, Yayasan, Lainnya
            $table->string('status', 30)->default('received'); // draft, sent, received, action_needed, archived
            $table->date('action_deadline')->nullable();
            $table->string('file_path', 255)->nullable();
            $table->string('file_original_name', 255)->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['school_id', 'type']);
            $table->index(['school_id', 'reference_number']);
            $table->index(['school_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('school_letters');
        Schema::dropIfExists('facility_incidents');
        Schema::dropIfExists('facility_reservations');
        Schema::dropIfExists('school_rooms');
        Schema::dropIfExists('consumable_stock_logs');
        Schema::dropIfExists('consumable_items');
        Schema::dropIfExists('asset_logs');
        Schema::dropIfExists('school_assets');
        Schema::dropIfExists('inventory_categories');
        Schema::dropIfExists('library_loans');
        Schema::dropIfExists('library_book_copies');
        Schema::dropIfExists('library_books');
    }
};
