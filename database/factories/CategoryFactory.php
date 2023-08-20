<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $date_to = $this->faker->dateTimeBetween('-20 years', 'now');
        $date_from = $this->faker->dateTimeInInterval($date_to, '- 1 year');
        $kateOrKumite = $this->faker->randomElement([0, 1]);
        $categoryName = $kateOrKumite == 1 ? $this->faker->randomElement(['Apsolutni nivo', 'I nivo', 'II nivo', 'III nivo']) : '-' ;
        return [
            'name' => $this->faker->randomElement(['Juniori', 'Kadeti', 'Seniori', 'U21', 'Nade', 'Poletarci']),
            'kata_or_kumite' => $kateOrKumite,
            'category_name' => $categoryName,
            'gender' => $this->faker->randomElement([1, 2, 3]),
            'date_from' => $date_from,
            'date_to' => $date_to,
            'solo_or_team' => $this->faker->randomElement([0, 1]),
            'match_lenght' => $this->faker->randomElement([10, 20]),
            'status' => 1,
        ];
    }
}

