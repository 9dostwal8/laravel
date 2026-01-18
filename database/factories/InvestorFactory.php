<?php

namespace Database\Factories;

use App\Enums\GenderEnum;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Investor>
 */
class InvestorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => ['en' => $this->faker->firstNameMale()],
            'gender' => GenderEnum::MALE,
            'nationality' => 'Iraq',
            'national_code' => $this->faker->numberBetween(100000000),
            'email' => $this->faker->companyEmail(),
            'first_phone_number' => $this->faker->phoneNumber(),
            'second_phone_number' => $this->faker->phoneNumber(),
            'passport_number' => $this->faker->numberBetween(10000),
            'address' => $this->faker->address(),
        ];
    }
}
