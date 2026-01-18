<?php

namespace Database\Factories;

use App\Enums\InvestmentTypeEnum;
use App\Enums\ProjectStatus;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Project>
 */
class ProjectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $landNumber = mt_rand(1000000, 10000000);

        return [
            'project_name' => ['en' => $this->faker->sentence(4)],
            'company_name' => $this->faker->sentence(4),
            'status' => ProjectStatus::IN_PROGRESS,
            'investment_type' => InvestmentTypeEnum::NATIONAL,
            'file_number' => Str::random(3).'-'.$this->faker->randomNumber(),
            'old_file_number' => $this->faker->randomNumber(),
            'license_number' => $this->faker->randomNumber(),
            'capital_dinar' => $this->faker->randomFloat(2, 1, 8),
            'capital_dollar' => $this->faker->randomFloat(2, 1, 8),
            'currency_rate' => $this->faker->randomFloat(2, 1, 8),
            'loan_fund' => $this->faker->randomFloat(2, 1, 8),
            'non_loan_fund' => $this->faker->randomFloat(2, 1, 8),
            'execution_time_years' => $this->faker->randomNumber(1),
            'execution_time_months' => $this->faker->randomNumber(1),
            'hectare_area' => $this->faker->randomNumber(2),
            'meter_area' => $this->faker->randomNumber(2),
            'project_location' => $this->faker->sentence(4),
            'place_of_land_allocation' => $this->faker->sentence(4),
            'land_number' => ["$landNumber"],
            'type_of_land_allocation' => $this->faker->sentence(4),
            'land_granting_organization' => $this->faker->sentence(4),
            'kurdistan_fixed_workforce_count' => $this->faker->randomNumber(2),
            'foreign_fixed_workforce_count' => $this->faker->randomNumber(2),
            'iraq_fixed_workforce_count' => $this->faker->randomNumber(2),
            'seperated_areas_fixed_workforce_count' => $this->faker->randomNumber(2),
            'kurdistan_temporary_workforce_count' => $this->faker->randomNumber(2),
            'foreign_temporary_workforce_count' => $this->faker->randomNumber(2),
            'iraq_temporary_workforce_count' => $this->faker->randomNumber(2),
            'seperated_areas_temporary_workforce_count' => $this->faker->randomNumber(2),
            'licence_received_at' => now(),
            'land_delivered_at' => now(),
            'started_at' => now(),
            'estimated_project_end_date' => now(),
            'actual_project_end_date' => now(),
            'requested_at' => now(),
        ];
    }
}
