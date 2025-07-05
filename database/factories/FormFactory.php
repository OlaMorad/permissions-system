<?php

namespace Database\Factories;

use App\Enums\FormStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Form>
 */
class FormFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(3, true),
            'status' => FormStatus::Active->value,
            'cost' => fake()->randomFloat(2, 10, 200),
        ];
    }
}
