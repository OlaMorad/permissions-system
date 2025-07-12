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
            'file' => 'files/8IyqTFqxGIEcLLlezb45thVZzFQMGBxUEKC1jJDQ.pdf',
            'image' => fake()->imageUrl(),
            'receipt' => 'receipts/VptYbM6OziNq8B0UqHNtvBBci8yF7OtD9NqKxbgN.jpg',
        ];
    }
}
