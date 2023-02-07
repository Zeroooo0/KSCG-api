<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;


/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Club>
 */
class ClubFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $name = fake()->firstName();
        $short_name = 'KK ' . $name;

        return [
            'name' => $name,
            'short_name' => $short_name,
            'town' => $this->faker->city(),
            'address' => $this->faker->streetAddress(),
            'country' => $this->faker->state(),
            'pib' => $this->faker->numberBetween(10000001, 99999999),
            'email' => $this->faker->email(),
            'phone_number' => $this->faker->phoneNumber(),
            'user_id' => User::factory(),
        ];
    }
    
}
