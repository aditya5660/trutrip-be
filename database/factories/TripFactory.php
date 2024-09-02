<?php

namespace Database\Factories;

use App\Models\Trip;
use Illuminate\Database\Eloquent\Factories\Factory;

class TripFactory extends Factory
{
    protected $model = Trip::class;

    public function definition()
    {
        return [
            'user_id' => 1,
            'title' => $this->faker->sentence,
            'origin' => $this->faker->city,
            'destination' => $this->faker->city,
            'start_date' => $this->faker->date,
            'end_date' => $this->faker->date,
            'trip_type' => $this->faker->randomElement(['single_day', 'multi_day']),
            'description' => $this->faker->paragraph,
        ];
    }
}
