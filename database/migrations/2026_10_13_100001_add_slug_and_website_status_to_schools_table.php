<?php

use App\Models\School;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->string('slug')->nullable()->unique()->after('name');
            $table->boolean('public_website_enabled')->default(true)->after('is_active');
        });

        // Populate slug for any existing schools
        foreach (School::all() as $school) {
            $baseSlug = Str::slug($school->short_name ?: $school->name);
            $slug = $baseSlug ?: 'sekolah-'.$school->id;
            $count = 1;
            while (School::where('slug', $slug)->where('id', '!=', $school->id)->exists()) {
                $slug = "{$baseSlug}-{$count}";
                $count++;
            }
            $school->slug = $slug;
            $school->save();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->dropColumn(['slug', 'public_website_enabled']);
        });
    }
};
