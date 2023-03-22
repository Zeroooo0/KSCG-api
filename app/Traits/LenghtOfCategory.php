<?php

namespace App\Traits;

use App\Models\Category;
use App\Models\Compatition;

trait LenghtOfCategory {
    public function countRegInCatOnCompa($competition, $category) {
        
        $registrations = $competition->registrations->where('category_id', $category->id)->count();
        $timePerCategory = $category->match_lenght;
        $repesaz = $category->repesaz == true ? $timePerCategory : 0;
        $totalTimePerCat = 0;
        //
        switch($registrations) {
            case $registrations <= 2:
                $totalTimePerCat = $registrations / 2 * $timePerCategory;
                break;
            case $registrations <= 4:
                $totalTimePerCat = $registrations / 2 * $timePerCategory + $timePerCategory + $repesaz;
                break;
            case $registrations <= 8:
                $totalTimePerCat = $registrations / 2 * $timePerCategory + 2 * $timePerCategory + $timePerCategory + 2 * $repesaz;
                break;
            case $registrations <= 16:
                $totalTimePerCat = $registrations / 2 * $timePerCategory + 4 * $timePerCategory + 2 * $timePerCategory + $timePerCategory + 3 * $repesaz;
                break;
            case $registrations <= 32:
                $totalTimePerCat = $registrations / 2 * $timePerCategory + 8 * $timePerCategory + 4 * $timePerCategory + 2 * $timePerCategory + $timePerCategory + 4 * $repesaz;
                break;
            case $registrations <= 64:
                $totalTimePerCat = $registrations / 2 * $timePerCategory + 16 * $timePerCategory + 8 * $timePerCategory + 4 * $timePerCategory + 2 * $timePerCategory + $timePerCategory + 5 * $repesaz;
                break;
        }
        return $totalTimePerCat;
            
    }

}