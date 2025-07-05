<?php

namespace Database\Factories;

use App\Enums\TransactionStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
            $paths = \App\Models\Path::pluck('id')->toArray();

        return [
        'from' => fake()->randomElement($paths),
        'to' => fake()->randomElement($paths),
        'sent_at' => now(),
        'received_at' => now()->addDay(),
        'status_from' => TransactionStatus::FORWARDED->value,
        'status_to' => TransactionStatus::PENDING->value,
        'receipt_number' => fake()->unique()->numerify('######'),
        'receipt_status' => 'pending',
            ];
    }
}
