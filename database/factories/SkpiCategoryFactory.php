<?php

namespace Database\Factories;

use App\Models\SkpiCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SkpiCategory>
 */
class SkpiCategoryFactory extends Factory
{
    protected $model = SkpiCategory::class;

    public function definition(): array
    {
        return [
            'curriculum_version' => '2026-V1',
            'code' => strtoupper(fake()->unique()->bothify('SKPI-###')),
            'name_id' => 'Kompetensi '.fake()->words(3, true),
            'name_en' => 'Competency '.fake()->words(3, true),
            'competency_domain' => fake()->randomElement(['Leadership', 'Technical', 'Communication', 'Social', 'Academic']),
            'is_mandatory' => false,
            'default_points' => fake()->randomElement([15, 20, 25, 30]),
            'level' => fake()->randomElement(['Lokal', 'Nasional', 'Internasional']),
        ];
    }

    public function mandatory(): static
    {
        return $this->state(fn () => ['is_mandatory' => true]);
    }
}
