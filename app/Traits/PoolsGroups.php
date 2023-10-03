<?php

namespace App\Traits;

use App\Models\Category;
use App\Models\Compatition;
use App\Models\Pool;
use Illuminate\Support\Arr;

trait PoolsGroups {

    public function sortGroups($groupsNo, $object, $catMatchLenght, $catTimeStart, $teamOrSingle)
    {
        // index one calculation
        // Taking data of registrations and number of groups
        $sortedGroups = [];
        $arrOfIndexes = [];
        //return $groupsNo;
        switch($groupsNo) {
            case $groupsNo == 1:
                $arrOfIndexes = [1];
                $pool = 1;
                break;
            case $groupsNo == 2:
                $arrOfIndexes = [1,2];
                $pool = 2;
                break;
            case $groupsNo == 4:
                $arrOfIndexes = [1,3,2,4];
                $pool = 3;
                break;
            case $groupsNo == 8:
                $arrOfIndexes = [1,5,3,7,2,6,4,8];
                $pool = 4;
                break;
            case $groupsNo == 16:
                $arrOfIndexes = [1,9,5,13,3,11,7,15,2,10,6,14,4,12,8,16];
                $pool = 5;
                break;
            case $groupsNo == 32:
                $arrOfIndexes = [1,17,9,25,3,19,11,27,5,21,13,29,7,23,15,31,2,18,10,26,4,20,12,28,6,22,14,30,8,24,16,32];
                $pool = 6;
                break;
            case $groupsNo == 64:
                $arrOfIndexes = [1,33,17,49,9,41,25,57,3,35,19,51,11,43,27,59,5,37,21,53,13,45,29,61,7,39,23,55,15,47,31,63,2,34,18,50,10,42,26,58,4,36,20,52,12,44,28,60,6,38,22,54,14,46,30,62,8,40,24,56,16,48,32,64];
                $pool = 7;
                break;
        }
        $timeTracking = $catTimeStart;
        for($j = 1; $j <= $pool; $j++) {
            $counting = $groupsNo;
            switch($j) {
                case $j == 1:
                    $counting = $groupsNo;
                    break;
                case $j == 2:
                    $counting = $groupsNo / 2;
                    break;
                case $j == 3:
                    $counting = $groupsNo / 4;
                    break;
                case $j == 4:
                    $counting = $groupsNo / 8;
                    break;
                case $j == 5:
                    $counting = $groupsNo / 16;
                    break;
                case $j == 6:
                    $counting = $groupsNo  / 32;
                    break;
                case $j == 7:
                    $counting = $groupsNo  / 64;
                    break;
            }
            
            switch($counting) {
                case $counting >= 4:
                    $groupType = 'G';
                    break;
                case $counting >= 2:
                    $groupType = 'SF';
                    break;
                case $counting = 1:
                    $groupType = 'FM';
                    break;
            }
                
            if($teamOrSingle == 'single') {
                for($i = 0; $i <= ($counting - 1); $i++) {
                    $random = rand(0,1);
                    $zeroIndexConvert = $arrOfIndexes[$i] - 1;
                    
                    $first = $random  ? $zeroIndexConvert : $zeroIndexConvert + $groupsNo;
                  
                    $second = $random ? $zeroIndexConvert + $groupsNo : $zeroIndexConvert;
                    
                    $input['compatition_id'] = Arr::get($object, '0.compatition_id');
                    $input['category_id'] = Arr::get($object, '0.category_id');
                    $input['pool'] = $j;
                    $input['pool_type'] = $groupType;
                    $input['group'] =  $i + 1;
                    
                    $input['status'] = 0;
                    $input['registration_one'] = $j == 1 ? Arr::get($object, $first . '.id') : null;
                    $input['registration_two'] = $j == 1 ? Arr::get($object, $second .  '.id') : null;
    
                    $input['start_time'] =  $timeTracking;
                    if($j == 1 && ($input['registration_one'] == null || $input['registration_two'] == null)) {
                        $timeTracking = $timeTracking;
                    } else {
                        $timeTracking = Date("H:i:s", strtotime("$timeTracking + $catMatchLenght minutes"));
                    }
                    
                    
                    $sortedGroups[] = $input;
                  
                }
            }
            if($teamOrSingle == 'team') {
                for($i = 0; $i <= ($counting - 1); $i++) {
                    $random = rand(0,1);
                    $zeroIndexConvert = $arrOfIndexes[$i] - 1;

                    $first = $random  ? $zeroIndexConvert : $zeroIndexConvert + $groupsNo;
                    $second = $random ? $zeroIndexConvert + $groupsNo : $zeroIndexConvert;

                    $input['compatition_id'] = Arr::get($object, '0.0.compatition_id');
                    $input['category_id'] = Arr::get($object, '0.0.category_id');
                    $input['pool'] = $j;
                    $input['pool_type'] = $groupType;
                    $input['group'] =  $i + 1;
                    $input['status'] = 0;
                    $input['team_one'] = $j == 1 ? Arr::get($object, $first . '.0.team_id') : null;
                    $input['team_two'] = $j == 1 ? Arr::get($object, $second .  '.0.team_id') : null;
    
                    $input['start_time'] =  $timeTracking;
                    if($j == 1 && ($input['team_one'] == null || $input['team_two'] == null)) {
                        $timeTracking = $timeTracking;
                    } else {
                        $timeTracking = Date("H:i:s", strtotime("$timeTracking + $catMatchLenght minutes"));
                    }
                    $sortedGroups[] = $input;
                }
            }

            
            
        }
        return $sortedGroups;
    }
    public function roundRobin($registrationsNo, $catMatchLenght, $catTimeStart, $object) 
    {
        
        $combinations = 0;
        $timeTracking = $catTimeStart;
        $sortedGroups = [];
        $arrOfIndexes = [];
        switch($registrationsNo) {
            case 3:
                $combinations = 5;
                $arrOfIndexes = [1,2,1,3,2,3];
                break;
            case 4:
                $combinations = 11;
                $arrOfIndexes = [1,2,1,3,1,4,2,3,2,4,3,4];
                break;
            case 5:
                $combinations = 7;
                $arrOfIndexes = [1,5,2,3,2,4,3,4];
                break;
            case 6:
                $combinations = 11;
                $arrOfIndexes = [1,6,1,2,6,2,3,4,3,5,4,5];
                break;
        }
        
        for($j = 1; $j <= 2; $j++) {  
            $j == 1 ? $combinations : $combinations = 1; 
            // if($j == 2) {
            //     return $combinations;
            // }
            $t = 1;
            for($i = 1; $i <= $combinations; $i = $i + 2) {
                $random = rand(0,1);
                if($j == 1) {
                    $groupType = $registrationsNo == 5 && $i == 1 ? 'SF' : 'RR';
                } else {
                    $groupType = 'RRFM';
                }
               
                
                $zeroIndexConvertOne = $arrOfIndexes[$i - 1] - 1;
                $zeroIndexConvertTwo = $arrOfIndexes[$i] - 1;
                // if($i == 5) {
                //     return " ovo je i $i $zeroIndexConvertOne $zeroIndexConvertTwo";
                // }
                $first = $random ? $zeroIndexConvertOne : $zeroIndexConvertTwo;
              
                $second = $random ? $zeroIndexConvertTwo: $zeroIndexConvertOne;
                
                $input['compatition_id'] = Arr::get($object, '0.compatition_id');
                $input['category_id'] = Arr::get($object, '0.category_id');
                $input['pool'] = $j;
                $input['pool_type'] = $groupType;
                $input['group'] =  $t;
                $t = $t + 1;
                $input['status'] = 0;
                $input['registration_one'] = $j == 1 ? Arr::get($object, $first . '.id') : null;
                $input['registration_two'] = $j == 1 ? Arr::get($object, $second .  '.id') : null;
    
                $input['start_time'] =  $timeTracking;
                if($j == 1 && ($input['registration_one'] == null || $input['registration_two'] == null)) {
                    $timeTracking = $timeTracking;
                } else {
                    $timeTracking = Date("H:i:s", strtotime("$timeTracking + $catMatchLenght minutes"));
                }
                
                
                $sortedGroups[] = $input;
              
            }
        }
        return $sortedGroups;
    }
    public function newSortGroups($groupsNo, $object, $catMatchLenght, $catTimeStart, $teamOrSingle, $registrationsNo) {
        $competition = Compatition::where('id', Arr::get($object, '0.compatition_id'))->first();
        $category = Category::where('id', Arr::get($object, '0.category_id'))->first();
        if($competition->rematch == 0) {
            return $this->sortGroups($groupsNo, $object, $catMatchLenght, $catTimeStart, $teamOrSingle);
        } else {
            if($category->repesaz == 0) {
                return $this->sortGroups($groupsNo, $object, $catMatchLenght, $catTimeStart, $teamOrSingle);
            } else {
                $roundRobinCount = [3,4,5,6];
                if(in_array($registrationsNo, $roundRobinCount)) {
                    
                    return $this->roundRobin($registrationsNo, $catMatchLenght, $catTimeStart, $object);
                } else {
                    return $this->sortGroups($groupsNo, $object, $catMatchLenght, $catTimeStart, $teamOrSingle);
                }
            }
        }
    }
    public function rematchBuilding($finalMatch) {
        $pools = Pool::where('compatition_id', $finalMatch->compatition_id)->where('category_id', $finalMatch->category_id)->where('pool', '<', $finalMatch->pool);
        $firstPlace = $pools->where('winner_id', $finalMatch->winner_id)->get();
        $secondPlace = $pools->where('winner_id', $finalMatch->looser_id)->get();
        $poolsCount = $finalMatch->pool - 1;
        $finalMatchStart = $finalMatch->start_time;
        $catMatchLenght = Category::where('id', $finalMatch->category_id)->first()->match_lenght;
        $timeTracking = Date("H:i:s", strtotime("$finalMatchStart + $catMatchLenght minutes"));
        $sortedGroups = [];
        $nextGroupTime = 0;
        for($j = 1; $j <= $poolsCount; $j++) {
            $groupType = $j == $poolsCount ?  "REFM" : "RE";
            $first = $j == 1 ? $firstPlace[0]->looser_id : null;
            $second = $firstPlace[$j]->looser_id;
    
            $input['compatition_id'] = $finalMatch->compatition_id;
            $input['category_id'] = $finalMatch->category_id;
            $input['pool'] = $j;
            $input['pool_type'] = $groupType;
            $input['group'] =  1;
            
            $input['status'] = 0;
            $input['registration_one'] = $first;
            $input['registration_two'] = $second;

            $input['start_time'] =  $timeTracking;
            if($j == 1 && ($input['registration_one'] == null || $input['registration_two'] == null)) {
                $timeTracking = $timeTracking;
            } else {
                $timeTracking = Date("H:i:s", strtotime("$timeTracking + $catMatchLenght minutes"));
                if($j == $poolsCount) {
                    $nextGroupTime = $timeTracking;
                }
            }
            $sortedGroups[] = $input;
            
        }
        for($k = 1; $k <= $poolsCount; $k++) {
            $groupType = $k == $poolsCount ?  "REFM" : "RE";
            $first = $k == 1 ? $secondPlace[0]->looser_id : null;
            $second = $secondPlace[$k]->looser_id;
    
            $inputNew['compatition_id'] = $finalMatch->compatition_id;
            $inputNew['category_id'] = $finalMatch->category_id;
            $inputNew['pool'] = $k;
            $inputNew['pool_type'] = $groupType;
            $inputNew['group'] =  2;
            
            $inputNew['status'] = 0;
            $inputNew['registration_one'] = $first;
            $inputNew['registration_two'] = $second;

            $inputNew['start_time'] =  $nextGroupTime;
            if($k == 1 && ($inputNew['registration_one'] == null || $inputNew['registration_two'] == null)) {
                $nextGroupTime = $nextGroupTime;
            } else {
                $nextGroupTime = Date("H:i:s", strtotime("$nextGroupTime + $catMatchLenght minutes"));
    
            }
            $sortedGroups[] = $inputNew;
        }

        return $sortedGroups;
    }
}