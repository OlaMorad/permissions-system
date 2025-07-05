<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Enums\Element_Type;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\FormElement>
 */
class FormElementFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'label' => fake()->words(2, true),
            'type' => fake()->randomElement([Element_Type::TEXT->value, Element_Type::NUMBER->value]),
        ];
    }
}
