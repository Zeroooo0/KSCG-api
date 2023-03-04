<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Compatition>
 */
class CompatitionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $start_time = $this->faker->dateTimeBetween('-1 years', 'now');
        return [
            'name' => $this->faker->randomElement(['Kup Oslobođenja', 'SENIORSKO PRVENSTVO CRNE GORE', 'PODGORIČKI POBJEDNIK', 'VII Grand Prix Montenegro 2022']),
            'country' => 'Montenegro',
            'city' => $this->faker->randomElement(['Podgorica', 'Bar', 'Bijelo Polje', 'Kotor', 'Berane', 'Ulcinj']),
            'address' => $this->faker->address(),
            'start_time_date' => $start_time,
            'registration_deadline' => $start_time->modify('-1 day'),
            'price_single' => $this->faker->randomElement([20, 30]),
            'price_team' => $this->faker->randomElement([30, 40]),
            'status' => 1,
            'tatami_no' => $this->faker->randomElement([4, 5, 6, 7, 8]),
            'host_name' => $this->faker->name(),
        ];
    }
}
