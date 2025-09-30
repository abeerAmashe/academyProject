<?php

namespace Database\Factories;

use App\Models\AcademyPending;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AcademyPhoto>
 */
class AcademyPhotoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'image' => $this->faker->imageUrl(),
            'academy_pending_id' => AcademyPending::inRandomOrder()->first()->id ?? AcademyPending::factory(),

        ];
    }
}