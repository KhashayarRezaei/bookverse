<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Book>
 */
class BookFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(3),
            'author' => $this->faker->name(),
            'description' => $this->faker->paragraph(),
            'price' => $this->faker->randomFloat(2, 5, 50),
            'published_year' => $this->faker->numberBetween(1900, 2024),
            'isbn' => $this->faker->unique()->isbn13(),
        ];
    }
}
