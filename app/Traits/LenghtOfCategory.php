<?php

namespace App\Traits;


trait LenghtOfCategory {

    public function categoryDuration($competition, $category) 
    {
        $registrations = $competition->registrations->where('category_id', $category->id);
        $timePerCategory = $category->match_lenght;
        $teamOrSingle = $category->solo_or_team;
        $teamRegistration = $registrations->groupBy('team_id')->count();
    
        $registrationCount = $teamOrSingle == 1 ? $registrations->count() : $teamRegistration;
    
        $repesaz = $category->repesaz == true ? $timePerCategory : 0;
        $totalTimePerCat = 0;
        $neededReg = 0;
        $groups = 0;
        $groupsReal = 0;
        $pools = 0;
        $pool = 0;
        //
        switch($registrationCount) {
            case $registrationCount <= 2:
                $totalTimePerCat = $registrationCount / 2 * $timePerCategory;
                $neededReg = 1;
                $groups = 0;
                $groupsReal = 1;
                $pools = 0;
                $pool = 1;
                break;
            case $registrationCount <= 4:
                $totalTimePerCat = $registrationCount / 2 * $timePerCategory + $timePerCategory + $repesaz;
                $neededReg = 3;
                $groups = 1;
                $groupsReal = 2;
                $pools = 1;
                $pool = 2;
                break;
            case $registrationCount <= 8:
                $totalTimePerCat = $registrationCount / 2 * $timePerCategory + 2 * $timePerCategory + $timePerCategory + 2 * $repesaz;
                $neededReg = 7;
                $groups = 3;
                $groupsReal = 4;
                $pools = 2;
                $pool = 3;
                break;
            case $registrationCount <= 16:
                $totalTimePerCat = $registrationCount / 2 * $timePerCategory + 4 * $timePerCategory + 2 * $timePerCategory + $timePerCategory + 3 * $repesaz;
                $neededReg = 15;
                $groups = 7;
                $groupsReal = 8;
                $pools = 3;
                $pool = 4;
                break;
            case $registrationCount <= 32:
                $totalTimePerCat = $registrationCount / 2 * $timePerCategory + 8 * $timePerCategory + 4 * $timePerCategory + 2 * $timePerCategory + $timePerCategory + 4 * $repesaz;
                $neededReg = 31;
                $groups = 15;
                $groupsReal = 16;
                $pools = 4;
                $pool = 5;
                break;
            case $registrationCount <= 64:
                $totalTimePerCat = $registrationCount / 2 * $timePerCategory + 16 * $timePerCategory + 8 * $timePerCategory + 4 * $timePerCategory + 2 * $timePerCategory + $timePerCategory + 5 * $repesaz;
                $neededReg = 64;
                $groups = 31;
                $groupsReal = 32;
                $pools = 5;
                $pool = 6;
                break;
        }
    return [
            'categoryDuration' =>$totalTimePerCat,
            'categoryGroupsFront' => $registrationCount > 0 ? $groups + 1 : 0,
            'categoryPoolsFront' => $registrationCount > 0 ? $pools + 1 : 0,
            'categoryGroupsBack' => $groups,
            'categoryPoolsBack' => $pools,
            'categoryRegistrations' => $registrationCount,
            'groupsReal' => $groupsReal,
            'pool' => $pool,
            'neededReg' => $neededReg

        ];
            
    }

}