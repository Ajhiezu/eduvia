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
        // 1. School Website Settings
        Schema::create('school_website_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete()->unique();
            $table->string('hero_headline')->nullable();
            $table->text('hero_subheadline')->nullable();
            $table->string('hero_cta_text')->nullable();
            $table->string('hero_cta_url')->nullable();
            $table->string('hero_image_path')->nullable();
            $table->string('hero_video_url')->nullable();
            $table->text('welcome_message')->nullable();
            $table->string('principal_name')->nullable();
            $table->string('principal_title')->nullable();
            $table->string('principal_photo_path')->nullable();
            $table->text('vision')->nullable();
            $table->json('mission')->nullable();
            $table->text('history')->nullable();
            $table->string('accreditation_grade', 50)->nullable();
            $table->string('accreditation_no', 100)->nullable();
            $table->text('maps_embed_url')->nullable();
            $table->json('social_links')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('meta_keywords')->nullable();
            $table->string('theme_color', 20)->default('#E11D2E');
            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });

        // 2. School Posts (News, Events, Achievements)
        Schema::create('school_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('type', 30)->default('news'); // 'news', 'event', 'achievement'
            $table->string('title');
            $table->string('slug');
            $table->text('excerpt')->nullable();
            $table->longText('content');
            $table->string('cover_image_path')->nullable();
            $table->string('category', 100)->nullable();
            $table->dateTime('event_date')->nullable();
            $table->dateTime('event_end_date')->nullable();
            $table->string('event_location')->nullable();
            $table->string('competition_name')->nullable();
            $table->string('competition_level', 50)->nullable();
            $table->string('awardee_name')->nullable();
            $table->boolean('is_published')->default(false)->index();
            $table->dateTime('published_at')->nullable();
            $table->integer('order_index')->default(0);
            $table->unsignedInteger('view_count')->default(0);
            $table->timestamps();

            $table->unique(['school_id', 'slug']);
            $table->index(['school_id', 'type', 'is_published']);
        });

        // 3. School Extracurriculars
        Schema::create('school_extracurriculars', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->string('category', 100);
            $table->text('description')->nullable();
            $table->string('coach_name')->nullable();
            $table->string('schedule_info')->nullable();
            $table->string('location')->nullable();
            $table->text('requirements')->nullable();
            $table->string('cover_image_path')->nullable();
            $table->boolean('is_published')->default(true)->index();
            $table->integer('order_index')->default(0);
            $table->timestamps();

            $table->unique(['school_id', 'slug']);
        });

        // 4. School Facilities & Featured Programs
        Schema::create('school_facilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('category', 50)->default('facility'); // 'facility', 'featured_program'
            $table->text('description')->nullable();
            $table->string('cover_image_path')->nullable();
            $table->boolean('is_published')->default(true)->index();
            $table->integer('order_index')->default(0);
            $table->timestamps();

            $table->index(['school_id', 'category', 'is_published']);
        });

        // 5. School Galleries
        Schema::create('school_galleries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('album_name');
            $table->string('title');
            $table->text('caption')->nullable();
            $table->string('media_path');
            $table->string('media_type', 20)->default('image'); // 'image', 'video'
            $table->string('video_url')->nullable();
            $table->string('alt_text')->nullable();
            $table->boolean('is_published')->default(true)->index();
            $table->integer('order_index')->default(0);
            $table->timestamps();

            $table->index(['school_id', 'album_name', 'is_published']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('school_galleries');
        Schema::dropIfExists('school_facilities');
        Schema::dropIfExists('school_extracurriculars');
        Schema::dropIfExists('school_posts');
        Schema::dropIfExists('school_website_settings');
    }
};
