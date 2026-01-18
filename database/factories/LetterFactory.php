<?php

namespace Database\Factories;

use App\Enums\LetterRecipientTypeEnum;
use App\Enums\LetterTypeEnum;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Letter>
 */
class LetterFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'letter_type' => LetterTypeEnum::ARRIVED,
            'recipient_type' => LetterRecipientTypeEnum::SENDER,
            'subject' => $this->faker->sentence(4),
            'attachment' => $this->faker->filePath(),
            'number' => $this->faker->randomNumber(5),
            'submitted_at' => now()->subDay(),
        ];
    }
}
