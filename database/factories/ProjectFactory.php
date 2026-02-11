<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
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
        return [
            'name' => $this->faker->company() . ' Project',
            'floors' => $this->faker->numberBetween(2, 30),
            'total_units' => $this->faker->numberBetween(10, 500),
            'aria_range' => $this->faker->numberBetween(1000, 15000) . ' m²',
            'location' => $this->faker->city(),
            'status' => $this->faker->randomElement(['active', 'completed', 'planning']),
            'notes' => $this->faker->optional()->text(100),
        ];
    }
}
