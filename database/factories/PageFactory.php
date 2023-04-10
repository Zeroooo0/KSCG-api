<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class PageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $title = $this->faker->sentence();
        $slug = str_replace(' ', '-', strtolower(trim($title, '.')));
        return [
            'title' => $title,
            'slug' => $slug,
            'content' => $this->faker->paragraph(5),
            'excerpt' => $this->faker->paragraph(2),
            'user_id' => User::all()->random()->id
        ];
    }
}
