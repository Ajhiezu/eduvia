<?php

namespace Database\Factories;

use App\Models\School;
use App\Models\SchoolAnnouncement;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<SchoolAnnouncement>
 */
class SchoolAnnouncementFactory extends Factory
{
    protected $model = SchoolAnnouncement::class;

    public function definition(): array
    {
        $title = fake()->sentence();

        return [
            'school_id' => School::factory(),
            'created_by' => User::factory(),
            'title' => $title,
            'slug' => Str::slug($title).'-'.Str::lower(Str::random(6)),
            'content' => fake()->paragraphs(3, true),
            'category' => fake()->randomElement(['general', 'academic', 'event', 'holiday', 'urgent']),
            'target_audience' => 'all',
            'grade_level_id' => null,
            'classroom_id' => null,
            'status' => SchoolAnnouncement::STATUS_PUBLISHED,
            'is_pinned' => false,
            'published_at' => now(),
            'expires_at' => null,
            'attachment_path' => null,
            'attachment_name' => null,
            'view_count' => 0,
        ];
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => SchoolAnnouncement::STATUS_DRAFT,
            'published_at' => null,
        ]);
    }

    public function forParents(): static
    {
        return $this->state(fn (array $attributes) => [
            'target_audience' => SchoolAnnouncement::AUDIENCE_PARENTS,
        ]);
    }
}
