<?php

namespace Database\Factories;

use App\Models\Crossword;
use App\Models\CrosswordLike;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CrosswordLike>
 */
class CrosswordLikeFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'crossword_id' => Crossword::factory(),
        ];
    }
}
