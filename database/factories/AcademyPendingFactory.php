<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\AcademyAdminstrator;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AcademyPending>
 */
class AcademyPendingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'academy_adminstrator_id' => AcademyAdminstrator::inRandomOrder()->first()->id 
                ?? AcademyAdminstrator::factory(), // يجيب أدمن عشوائي أو يعمل واحد جديد
            
            'name' => $this->faker->company,
            'description' => $this->faker->sentence(10),
            'location' => $this->faker->city,
            'license_number' => $this->faker->unique()->bothify('LIC-####'),
            
            // boolean fields
            'english' => $this->faker->boolean,
            'germany' => $this->faker->boolean,
            'spanish' => $this->faker->boolean,
            'french' => $this->faker->boolean,
            
            'photo' => $this->faker->imageUrl(640, 480, 'academy', true),
        ];
    }
}