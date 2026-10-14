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
        Schema::create('grade_levels', function (Blueprint $table) {
            $table->id();
            $table->string('educational_level'); // 'sd', 'smp', 'sma', 'smk'
            $table->unsignedTinyInteger('level_number'); // 1 - 12
            $table->string('name'); // e.g. "Kelas 7", "Kelas 10"
            $table->unsignedSmallInteger('order')->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['educational_level', 'level_number']);
            $table->index('educational_level');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grade_levels');
    }
};
