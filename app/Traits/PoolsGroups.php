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
        $targetCat = $teamOrSingle == 'single' ? Arr::get($object, '0.category_id') : Arr::get($object, '0.0.category_id');
        $targetCompetition = $teamOrSingle == 'single' ? Arr::get($object, '0.compatition_id') : Arr::get($object, '0.0.compatition_id');
        $category = Category::where('id', $targetCat)->first();
        $competition = Compatition::where('id', $targetCompetition )->first();

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
    public function firstPoolKataRematch( $objectIterating, $groupType, $poolsWithComp, $groupTotal, $catMatchLenght, $catTimeStart, $teamOrSingle) { 
            $timeTracking = $catTimeStart;
            $startingIndex = 0;
            $poolsCount = 1;
            $finishedGroup = [];
            for($p = 1; $p <= $poolsWithComp; $p++) {
                $groupTotal == 1 ? $groupsInPool = 1 : $groupsInPool = 2;
                for($g = 1; $g <= $groupsInPool; $g++){
                    for($i = $startingIndex; $i < count($objectIterating); $i = $i + $groupTotal) {     
                        $input['compatition_id'] = $teamOrSingle == 'single' ? Arr::get($objectIterating, '0.compatition_id') : Arr::get($objectIterating, '0.0.compatition_id');
                        $input['category_id'] = $teamOrSingle == 'single' ? Arr::get($objectIterating, '0.category_id') : Arr::get($objectIterating, '0.0.category_id');
                        $input['pool_type'] = $groupType;
                        $input['pool'] = $p;
                        $input['group'] =  $g;
                        $input['status'] = 0;
                        $teamOrSingle == 'single' ? $input['registration_one'] = Arr::get($objectIterating, $i . '.id') : $input['team_one'] = Arr::get($objectIterating, $i . '.0.team_id');
                        $teamOrSingle == 'single' ? $input['registration_two'] = null : $input['team_two'] = null;
                        $input['start_time'] =  $timeTracking;
                        $timeTracking = Date("H:i:s", strtotime("$timeTracking + $catMatchLenght minutes"));
                        $finishedGroup[] = $input;
                    }
                    $startingIndex++;
                }
                $poolsCount++;
            }

            $shouldCreateG = false;
            $shouldCreateSFM = false;
            $shouldCreateFM = false;
            $groupCount = 0;
            switch($groupType) {
                case 'KRG4':
                    $shouldCreateG = false;
                    $shouldCreateSFM = false;
                    $shouldCreateFM = true;
                    break;
                case 'KRG10':
                    $shouldCreateG = false;
                    $shouldCreateSFM = true;
                    $shouldCreateFM = true;
                    break;
                case 'KRG24':
                    $shouldCreateG = true;
                    $groupCount = $poolsCount;
                    $shouldCreateSFM = true;
                    $shouldCreateFM = true;
                    break;
                case 'KRG48':
                    $shouldCreateG = true;
                    $groupCount = $poolsCount + 2;
                    $shouldCreateSFM = true;
                    $shouldCreateFM = true;
                    break;
                case 'KRG96':
                    $shouldCreateG = true;
                    $groupCount = $poolsCount + 6;
                    $shouldCreateSFM = true;
                    $shouldCreateFM = true;
                    break;
                
            }
            if($shouldCreateG) {
                for($po = $poolsCount; $po <= $groupCount; $po++) {
                    for($gr = 1; $gr <= 2; $gr++){
                        for($it = 0; $it <= 3; $it++) {
                            $input['compatition_id'] = $teamOrSingle == 'single' ? Arr::get($objectIterating, '0.compatition_id') : Arr::get($objectIterating, '0.0.compatition_id');
                            $input['category_id'] = $teamOrSingle == 'single' ? Arr::get($objectIterating, '0.category_id') : Arr::get($objectIterating, '0.0.category_id');
                            $input['pool_type'] = "KRGA";
                            $input['pool'] = $po;
                            $input['group'] = $gr;
                            $input['status'] = 0;
                            $teamOrSingle == 'single' ? $input['registration_one'] = null : $input['team_one'] = null;
                            $teamOrSingle == 'single' ? $input['registration_two'] = null : $input['team_two'] = null;
                            $input['start_time'] =  $timeTracking;
                            $timeTracking = Date("H:i:s", strtotime("$timeTracking + $catMatchLenght minutes"));
                            $finishedGroup[] = $input;
                        }
                    }
                    $poolsCount++;
                }
            }
            if($shouldCreateSFM) {
                $finalPool = $poolsCount;
                for($gro = 1; $gro <= 2; $gro++){
                    $input['compatition_id'] = $teamOrSingle == 'single' ? Arr::get($objectIterating, '0.compatition_id') : Arr::get($objectIterating, '0.0.compatition_id');
                    $input['category_id'] = $teamOrSingle == 'single' ? Arr::get($objectIterating, '0.category_id') : Arr::get($objectIterating, '0.0.category_id');
                    $input['pool_type'] = "KRSF";
                    $input['pool'] = $finalPool;
                    $input['group'] = $gro;
                    $input['status'] = 0;
                    $teamOrSingle == 'single' ? $input['registration_one'] = null : $input['team_one'] = null;
                    $teamOrSingle == 'single' ? $input['registration_two'] = null : $input['team_two'] = null;
                    $input['start_time'] =  $timeTracking;
                    $timeTracking = Date("H:i:s", strtotime("$timeTracking + $catMatchLenght minutes"));
                    $finishedGroup[] = $input;
                }
            }
            if($shouldCreateFM) {
                $finalPool = $poolsCount;
                $input['compatition_id'] = $teamOrSingle == 'single' ? Arr::get($objectIterating, '0.compatition_id') : Arr::get($objectIterating, '0.0.compatition_id');
                $input['category_id'] = $teamOrSingle == 'single' ? Arr::get($objectIterating, '0.category_id') : Arr::get($objectIterating, '0.0.category_id');
                $input['pool_type'] = "KRFM";
                $input['pool'] = $finalPool;
                $input['group'] = 3;
                $input['status'] = 0;
                $teamOrSingle == 'single' ? $input['registration_one'] = null : $input['team_one'] = null;
                $teamOrSingle == 'single' ? $input['registration_two'] = null : $input['team_two'] = null;
                $input['start_time'] =  $timeTracking;
                $timeTracking = Date("H:i:s", strtotime("$timeTracking + $catMatchLenght minutes"));
                $finishedGroup[] = $input;
            }
            return $finishedGroup;
        }
    public function sortKataRepGroups($registrationNo, $object, $catMatchLenght, $catTimeStart, $teamOrSingle)
    {
        // index one calculation
        // Taking data of registrations and number of groups
        $sortedGroups = [];

        switch($registrationNo) {
            case $registrationNo <= 3:
                $groupTotal = 1;
                $poolsWithComp = 1;
                $groupType = 'KRG3';
                $sortedGroups[] = $this->firstPoolKataRematch( $object, $groupType, $poolsWithComp, $groupTotal, $catMatchLenght, $catTimeStart, $teamOrSingle);
                break;
            case $registrationNo == 4:
                $groupTotal = 2;
                $poolsWithComp = 1;
                $groupType = 'KRG4';
                $sortedGroups[] = $this->firstPoolKataRematch( $object, $groupType, $poolsWithComp, $groupTotal, $catMatchLenght, $catTimeStart, $teamOrSingle);
                break;
            case $registrationNo <= 10:
                $groupTotal = 2;
                $poolsWithComp = 1;
                $groupType = 'KRG10';
                $sortedGroups[] = $this->firstPoolKataRematch( $object, $groupType, $poolsWithComp, $groupTotal, $catMatchLenght, $catTimeStart, $teamOrSingle);
                break;
            case $registrationNo <= 24:
                $groupTotal = 2;
                $poolsWithComp = 1;
                $groupType = 'KRG24';
                $sortedGroups[] = $this->firstPoolKataRematch( $object, $groupType, $poolsWithComp, $groupTotal, $catMatchLenght, $catTimeStart, $teamOrSingle);
                break;
            case $registrationNo <= 48:
                $groupTotal = 4;
                $poolsWithComp = 2;
                $groupType = 'KRG48';
                $sortedGroups[] = $this->firstPoolKataRematch( $object, $groupType, $poolsWithComp, $groupTotal, $catMatchLenght, $catTimeStart, $teamOrSingle);
                break;
            case $registrationNo <= 98:
                $groupTotal = 8;
                $poolsWithComp = 4;
                $groupType = 'KRG98';
                $sortedGroups[] = $this->firstPoolKataRematch( $object, $groupType, $poolsWithComp, $groupTotal, $catMatchLenght, $catTimeStart, $teamOrSingle);
                break;
            case $registrationNo <= 128:
                $groupTotal = 16;
                $poolsWithComp = 8;
                $groupType = 'KRG192';
                $sortedGroups[] = $this->firstPoolKataRematch( $object, $groupType, $poolsWithComp, $groupTotal, $catMatchLenght, $catTimeStart, $teamOrSingle);
                break;
        }        
        return Arr::collapse($sortedGroups);
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
                    $groupType = $registrationsNo == 5 && $i == 1 ? 'RRSF' : 'RR';
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
        $competition = $teamOrSingle == 'single' ? Compatition::where('id', Arr::get($object, '0.compatition_id'))->first() : Compatition::where('id', Arr::get($object, '0.0.compatition_id'))->first();
        $category = $teamOrSingle == 'single' ? Category::where('id', Arr::get($object, '0.category_id'))->first() : Category::where('id', Arr::get($object, '0.0.category_id'))->first();
        if($teamOrSingle == 'single' && $competition->rematch == 0) {
            return $this->sortGroups($groupsNo, $object, $catMatchLenght, $catTimeStart, $teamOrSingle);
        } else {
            if($category->repesaz == 0) {
                return $this->sortGroups($groupsNo, $object, $catMatchLenght, $catTimeStart, $teamOrSingle);
            } 
            if($category->repesaz == 1 && $category->kata_or_kumite == 0) {
                //$roundRobinCount = [3,4,5,6];
                //if(in_array($registrationsNo, $roundRobinCount)) {
                //    
                //   return $this->roundRobin($registrationsNo, $catMatchLenght, $catTimeStart, $object);
                //} else {
                //    return $this->sortGroups($groupsNo, $object, $catMatchLenght, $catTimeStart, $teamOrSingle);
                //}
            }
            if($category->repesaz == 1 && $category->kata_or_kumite == 1) {
                return $this->sortKataRepGroups($registrationsNo, $object, $catMatchLenght, $catTimeStart, $teamOrSingle);
                
            }
        }
        if($teamOrSingle == 'team' && $competition->rematch == 0) {
            //return $this->sortGroups($groupsNo, $object, $catMatchLenght, $catTimeStart, $teamOrSingle);
        } else {
            if($category->repesaz == 1 && $category->kata_or_kumite == 1) {
                return $this->sortKataRepGroups($registrationsNo, $object, $catMatchLenght, $catTimeStart, $teamOrSingle);
            }
            //return $this->sortGroups($groupsNo, $object, $catMatchLenght, $catTimeStart, $teamOrSingle);
        }
    }
    public function rematchBuilding($finalMatch) {
        $pools = Pool::where('compatition_id', $finalMatch->compatition_id)->where('category_id', $finalMatch->category_id)->where('pool', '<', $finalMatch->pool)->get();
        
        $firstPlace = $pools->where('winner_id', $finalMatch->winner_id)->values();
        $secondPlace = $pools->where('winner_id', $finalMatch->looser_id)->values();
        $firstPlaceCount = $firstPlace->count() - 1;
        $secondPlaceCount = $secondPlace->count() - 1;
        $poolsCount = $finalMatch->pool - 1;
        $finalMatchStart = $finalMatch->start_time;
        $catMatchLenght = Category::where('id', $finalMatch->category_id)->first()->match_lenght;
        $timeTracking = Date("H:i:s", strtotime("$finalMatchStart + $catMatchLenght minutes"));
        $sortedGroups = [];
        $nextGroupTime = 0;
        for($j = 1; $j <= $firstPlaceCount; $j++) {
            $groupType = $j == $firstPlaceCount ?  "REFM" : "RE";
        
            $first = $j == 1 ? Arr::get($firstPlace, '0.looser_id') : null;
            $second = Arr::get($firstPlace, $j . '.looser_id');

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
                if($j == $firstPlaceCount) {
                    $nextGroupTime = $timeTracking;
                }
            }
            $sortedGroups[] = $input;
            
        }
        for($k = 1; $k <= $secondPlaceCount; $k++) {
            $groupType = $k == $secondPlaceCount ?  "REFM" : "RE";
            $first = $k == 1 ? Arr::get($secondPlace,  '0.looser_id') : null;
            $second = Arr::get($secondPlace, $k . '.looser_id');
            
    
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