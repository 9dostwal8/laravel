<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Progress>
 */
class ProgressFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'progress_percentage' => $this->faker->numberBetween(0, 100),
            'description' => $this->faker->sentence(6),
            'visited_at' => $this->faker->dateTime,
        ];
    }
}
