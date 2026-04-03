<?php

namespace Database\Factories;

use App\Models\RoadmapItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RoadmapItem>
 */
class RoadmapItemFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'type' => fake()->randomElement(['feature', 'fix', 'improvement']),
            'status' => 'planned',
            'sort_order' => 0,
            'target_date' => fake()->optional()->dateTimeBetween('now', '+6 months'),
        ];
    }

    public function feature(): static
    {
        return $this->state(fn () => ['type' => 'feature']);
    }

    public function fix(): static
    {
        return $this->state(fn () => ['type' => 'fix']);
    }

    public function improvement(): static
    {
        return $this->state(fn () => ['type' => 'improvement']);
    }

    public function planned(): static
    {
        return $this->state(fn () => ['status' => 'planned']);
    }

    public function inProgress(): static
    {
        return $this->state(fn () => ['status' => 'in_progress']);
    }

    public function completed(): static
    {
        return $this->state(fn () => [
            'status' => 'completed',
            'completed_date' => fake()->dateTimeBetween('-3 months', 'now'),
        ]);
    }
}
