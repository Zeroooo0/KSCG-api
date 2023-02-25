<?php

namespace Database\Factories;

use App\Models\Club;
use App\Models\Compatition;
use App\Models\Compatitor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Registration>
 */
class RegistrationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $compatitor = Compatitor::all()->random();
        $compatitiorId = $compatitor->id;
        $clibId = $compatitor->club_id;
        $compatition = Compatition::find(1)->first();
        $category = $compatition->categories->random();
        return [
            'compatition_id' => $compatition->id,
            'club_id' => $clibId,
            'compatitor_id' => $compatitiorId,
            'category_id' => $category->id,
            'team_or_single' => $category->solo_or_team,
            'team_id'
        ];
    }
}

