<?php

namespace App\Traits;


trait LenghtOfCategory {

    public function categoryDuration($competition, $category) 
    {
        $registrations = $competition->registrations->where('category_id', $category->id);
        $timePerCategory = $category->match_lenght;
        $teamOrSingle = $category->solo_or_team;
        $teamRegistration = $registrations->where('team_id', '!=', null)->groupBy('team_id')->count();
    
        $registrationCount = $teamOrSingle == 1 ? $registrations->count() : $teamRegistration;
    
        $repesaz = $category->repesaz == true ? $timePerCategory : 0;
        $totalTimePerCat = 0;
        $groups = 0;
        $pools = 0;
        //
        switch($registrationCount) {
            case $registrationCount <= 2:
                $totalTimePerCat = $registrationCount / 2 * $timePerCategory;
                $groups = 0;
                $pools = 0;
                break;
            case $registrationCount <= 4:
                $totalTimePerCat = $registrationCount / 2 * $timePerCategory + $timePerCategory + $repesaz;
                $groups = 1;
                $pools = 1;
                break;
            case $registrationCount <= 8:
                $totalTimePerCat = $registrationCount / 2 * $timePerCategory + 2 * $timePerCategory + $timePerCategory + 2 * $repesaz;
                $groups = 3;
                $pools = 2;
                break;
            case $registrationCount <= 16:
                $totalTimePerCat = $registrationCount / 2 * $timePerCategory + 4 * $timePerCategory + 2 * $timePerCategory + $timePerCategory + 3 * $repesaz;
                $groups = 7;
                $pools = 3;
                break;
            case $registrationCount <= 32:
                $totalTimePerCat = $registrationCount / 2 * $timePerCategory + 8 * $timePerCategory + 4 * $timePerCategory + 2 * $timePerCategory + $timePerCategory + 4 * $repesaz;
                $groups = 15;
                $pools = 4;
                break;
            case $registrationCount <= 64:
                $totalTimePerCat = $registrationCount / 2 * $timePerCategory + 16 * $timePerCategory + 8 * $timePerCategory + 4 * $timePerCategory + 2 * $timePerCategory + $timePerCategory + 5 * $repesaz;
                $groups = 31;
                $pools = 5;
                break;
        }
        return [
            'categoryDuration' =>$totalTimePerCat,
            'categoryGroupsFront' => $groups > 0 ? $groups + 1 : 0,
            'categoryPoolsFront' => $pools > 0 ? $pools + 1 : 0,
            'categoryGroupsBack' => $groups,
            'categoryPoolsBack' => $pools,
        ];
            
    }

}