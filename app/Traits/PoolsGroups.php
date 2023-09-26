<?php

namespace App\Traits;

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
    public function roundRobin($registrationsNo, $catMatchLenght, $catTimeStart, $objectSmall, $object) 
    {
        if($registrationsNo == 3 || $registrationsNo == 4){
            $combinations = 0;
            switch($registrationsNo) {
                case 3:
                    $combinations = 3;
                    $arrOfIndexes = [0,1,0,2,1,2];
                    break;
                case 4:
                    $combinations = 6;
                    $arrOfIndexes = [0,1,0,2,0,3,1,2,1,3,2,3];
                    break;
            }

        }

        
    }
}