<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\FormMedia>
 */
class FormMediaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'file' => fake()->filePath(),
            'image' => fake()->imageUrl(),
            'receipt' => 'FAKE-RECEIPT-' . fake()->unique()->randomNumber(5),
        ];
    }
}
