<?php

namespace Database\Factories;

use App\Models\Lyric;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Lyric>
 */
class LyricFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title'=>fake()->sentence(),
            'artist'=>fake()->name(),
            'album'=>fake()->word(),
            'duration'=>[
                'minutes'=>fake()->numberBetween(0,59),
                'seconds'=>fake()->numberBetween(0,59)
            ],
            'offset'=>fake()->numberBetween(-30000,30000),
            'content'=>fake()->paragraphs(8,true)
        ];
    }
}
