<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SpecialPersonal>
 */
class SpecialPersonalFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $gender = $this->faker->randomElement(['M', 'Ž']);
        $name =  $gender == 'M' ? $this->faker->firstNameMale() : $this->faker->firstNameFemale();
        return [
            'name' => $name,
            'last_name' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'phone_number' => $this->faker->phoneNumber(),
            'rolle' => $this->faker->randomElement([0, 1, 2, 3]),
            'status' => true,
            'gender' => $gender,
            'country' => $this->faker->state()
        ];
    }
}
