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
        Schema::table('subjects', function (Blueprint $table) {
            $table->string('curriculum', 50)->nullable()->default('merdeka')->after('group');
            $table->string('educational_level', 20)->nullable()->after('curriculum'); // sd, smp, sma, smk
            $table->unsignedTinyInteger('credit_hours')->default(2)->after('educational_level'); // JP / SKS
            $table->string('academic_program', 100)->nullable()->after('credit_hours'); // MIPA, IPS, TKJ, dll.
        });

        Schema::table('classrooms', function (Blueprint $table) {
            $table->string('academic_program', 100)->nullable()->after('capacity'); // MIPA, IPS, TKJ, dll.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subjects', function (Blueprint $table) {
            $table->dropColumn(['curriculum', 'educational_level', 'credit_hours', 'academic_program']);
        });

        Schema::table('classrooms', function (Blueprint $table) {
            $table->dropColumn(['academic_program']);
        });
    }
};
