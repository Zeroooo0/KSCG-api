<?php

namespace Database\Factories;

use App\Models\Belt;
use App\Models\Club;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class CompatitorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $gender = $this->faker->randomElement([1, 2]);
        $name =  $gender == 1 ? $this->faker->firstNameMale() : $this->faker->firstNameFemale();
        $compatitor_id = 'MNE' . $this->faker->numberBetween(100000, 999999);
        return [
            'club_id' => Club::all()->random()->id,
            'name' => $name,
            'last_name' => $this->faker->lastName(),
            'gender' => $gender,
            'jmbg' => $this->faker->numberBetween(1000000000001, 9999999999999),
            'status' => $this->faker->randomElement([true, false]),
            'date_of_birth' => $this->faker->dateTimeBetween($startDate = '-13 years', $endsDate = '-7 years'),
            'kscg_compatitor_id' => $compatitor_id,
            'belt_id' => Belt::all()->random()->id,
            'weight' => $this->faker->numberBetween(20, 99)
        ];
    }
}
