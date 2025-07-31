<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'total_amount' => $this->faker->randomFloat(2, 10, 200),
            'payment_method' => $this->faker->randomElement(['stripe', 'paypal']),
            'status' => $this->faker->randomElement(['pending', 'paid', 'shipped', 'delivered', 'cancelled']),
        ];
    }
}
