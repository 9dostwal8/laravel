<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Amenity>
 */
class AmenityFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => ['en' => $this->faker->sentence(), 'ckb' => $this->faker->sentence()],
            'has_production_rate' => mt_rand(1, 100) % 2 === 0,
        ];
    }
}
