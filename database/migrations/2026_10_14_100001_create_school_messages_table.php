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
        Schema::create('school_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('reference_number', 50)->unique();
            $table->string('name', 150);
            $table->string('email', 150);
            $table->string('phone', 30)->nullable();
            $table->string('category', 50)->default('informasi_sekolah');
            $table->string('subject', 200);
            $table->text('message');
            $table->string('status', 30)->default('new'); // 'new', 'in_progress', 'resolved', 'archived'
            $table->boolean('is_read')->default(false);
            $table->dateTime('read_at')->nullable();
            $table->text('internal_notes')->nullable();
            $table->dateTime('resolved_at')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            // Indexes for fast searching, filtering, and tenant isolation
            $table->index(['school_id', 'status']);
            $table->index(['school_id', 'is_read']);
            $table->index(['school_id', 'category']);
            $table->index(['school_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('school_messages');
    }
};
