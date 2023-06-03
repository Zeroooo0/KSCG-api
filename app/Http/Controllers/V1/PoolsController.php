<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\PoolResource;
use App\Http\Resources\PoolsTeamResource;
use App\Models\Category;
use App\Models\Compatition;
use App\Models\Pool;
use App\Models\PoolTeam;
use App\Models\Registration;
use App\Models\Team;
use App\Models\TimeTable;
use App\Traits\HttpResponses;
use App\Traits\LenghtOfCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;

class PoolsController extends Controller
{
    use HttpResponses;
    use LenghtOfCategory;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return PoolResource::collection(Pool::all());
    }
    
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function automatedStore(Request $request, Compatition $compatition)
    {
        if(Auth::user()->user_type == 0 || Auth::user()->user_type == 1 && Auth::user()->status == 0 ) {
            return $this->error('', 'Not allowed!', 403);
        }
        $timeTable = $compatition->timeTable;
        $timeTableCategories = [];
        foreach($timeTable as $timeTableCategory) {
            $timeTableCategories [] = $timeTableCategory->category_id;
        }
        
        
        $registrations = $compatition->registrations;
        $reg_single = $registrations->where('team_or_single', 1)->whereIn('category_id', $timeTableCategories)->countBy('category_id');
        $reg_teams = $registrations->where('team_or_single', 0)->whereIn('category_id', $timeTableCategories)->countBy('category_id');
        $pools = $compatition->pools;
        $poolsTeam = $compatition->poolsTeam;
        
        $nn_single_cat = [];
        $nn_team_cat = [];
        $singleArr = [];
        $teamArr = [];
        
        $requestedCategory = $compatition->categories->where('id', $request->categoryId)->first();

        foreach($reg_single as $key=>$count){
            $nn_single_cat[] = $registrations->sortBy('club_id')->where('category_id', $key)->values();
        }
        foreach($reg_teams as $key=>$count){
            $nn_team_cat[] = $registrations->where('category_id', $key)->groupBy('team_id')->values();
        }
  
        
        if($pools->count() > 0 ) {
            Pool::destroy($pools);
            
        }
        if($poolsTeam->count() > 0) {
            PoolTeam::destroy($poolsTeam);
        }

        if($timeTable->count() == 0) {
            return $this->error('', 'Potrebno je prvo da se odredi Time Table', 422);
        }
     
        /** Here we start rebuilding */
        //return $nn_team_cat;
        
        foreach($nn_team_cat as $key => $val) {
            $category_id =  $val[0][0]->category_id;
            $timeTableData = $timeTable->where('category_id', $category_id)->first();
            $category_timeStart = $timeTableData->eto_start == null ? null : $timeTableData->eto_start;
            $category = Category::where('id', $category_id)->first();
            $category_match_lenght = $category->match_lenght;

            
            $timeTracking = $category_timeStart;
            $registrations = $compatition->registrations->where('category_id', $category->id);
            $timePerCategory = $category->match_lenght;
            $teamOrSingle = $category->solo_or_team;
            $teamRegistration = $registrations->groupBy('team_id')->count();
        
            $registrationCount = $teamOrSingle == 1 ? $registrations->count() : $teamRegistration;
        
            $repesaz = $category->repesaz == true ? $timePerCategory : 0;
            $totalTimePerCat = 0;
            $groups = 0;
            $pools = 0;
            $pool = 0;
            
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
                    $neededReg = 63;
                    $groups = 31;
                    $groupsReal = 32;
                    $pools = 5;
                    $pool = 6;
                    break;
            }
            
            for($j = 1; $j <= $pool; $j++) {
                $counting = $groupsReal;
                switch($j) {
                    case $j == 1:
                        $counting = $groupsReal;
                        break;
                    case $j == 2:
                        $counting = $groupsReal / 2;
                        break;
                    case $j == 3:
                        $counting = $groupsReal / 4;
                        break;
                    case $j == 4:
                        $counting = $groupsReal / 8;
                        break;
                    case $j == 5:
                        $counting = $groupsReal / 16;
                        break;
                    case $j == 6:
                        $counting = $groupsReal  / 32;
                        break;
                    case $j == 7:
                        $counting = $groupsReal  / 64;
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
                
                for($i = 0; $i <= ($counting -1 ); $i++) {
                    $random = rand(0,1);
                    $first = $random ? $i : $neededReg - $i;
                    $second = $random ? $neededReg - $i : $i;
                    $inputTeam['compatition_id'] = $compatition->id;
                    $inputTeam['category_id'] = $category_id;
                    $inputTeam['pool'] = $j;
                    $inputTeam['pool_type'] = $groupType;
                    $inputTeam['group'] = $i + 1;
                    $inputTeam['start_time'] = $timeTracking;
                    $inputTeam['status'] = 0;
                    $inputTeam['team_one'] = $j == 1 ? Arr::get($val,  $first . '.0.team_id') : null;
                    $inputTeam['team_two'] = $j == 1 ? Arr::get($val,  $second . '.0.team_id') : null;

                    if($j == 1 && ($inputTeam['team_one'] == null || $inputTeam['team_two'] == null)) {
                        $timeTracking = $timeTracking;
                    } else {
                        $timeTracking = Date("H:i:s", strtotime("$timeTracking + $category_match_lenght minutes"));
                    }


                    $teamArr[] = $inputTeam;
                }
            }
        }
       
        
        
        foreach($nn_single_cat as $val) {
            $category_id =  $val[0]->category_id;
            $timeTableData = $timeTable->where('category_id', $category_id)->first();
            $category_timeStart = $timeTableData->eto_start == null ? null : $timeTableData->eto_start;
            $category = Category::where('id', $category_id)->first();
            $category_match_lenght = $category->match_lenght;
            $cat_ids = $val->groupBy('club_id')->sortDesc();
            $sorted_cats = [];
            $startPoint = 0;
            foreach($cat_ids as $item=>$key) {
                $sorted_cats[] = $key;
            }
        
            $cleaned = Arr::collapse($sorted_cats);

            
            $timeTracking = $category_timeStart;

            $registrations = $compatition->registrations->where('category_id', $category->id);
            $timePerCategory = $category->match_lenght;
            $teamOrSingle = $category->solo_or_team;
            $teamRegistration = $registrations->groupBy('team_id')->count();
        
            $registrationCount = $teamOrSingle == 1 ? $registrations->count() : $teamRegistration;
        
            $repesaz = $category->repesaz == true ? $timePerCategory : 0;
            $totalTimePerCat = 0;
            $groups = 0;
            $pools = 0;
            $pool = 0;
            
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
                    $neededReg = 63;
                    $groups = 31;
                    $groupsReal = 32;
                    $pools = 5;
                    $pool = 6;
                    break;
            }
            for($j = 1; $j <= $pool; $j++) {
                $counting = $groupsReal;
                switch($j) {
                    case $j == 1:
                        $counting = $groupsReal;
                        break;
                    case $j == 2:
                        $counting = $groupsReal / 2;
                        break;
                    case $j == 3:
                        $counting = $groupsReal / 4;
                        break;
                    case $j == 4:
                        $counting = $groupsReal / 8;
                        break;
                    case $j == 5:
                        $counting = $groupsReal / 16;
                        break;
                    case $j == 6:
                        $counting = $groupsReal  / 32;
                        break;
                    case $j == 7:
                        $counting = $groupsReal  / 64;
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


                if($groupsReal > 4 && $j == 1) {
                
                    $startPoint = 1;
                    $groupCount =  $groupsReal / 4;
                    $compInGroup =  $neededReg / 4;
      
                    $groupOne = [];
                    $groupTwo = [];
                    $groupThree = [];
                    $groupFour = [];
      
                    $countingSteps = 0;
                    foreach($cleaned as $key) {
                       
                        switch($countingSteps) {
                            case 0:
                                $groupOne [] = $key;
                                $countingSteps = $countingSteps + 1;
                                break;
                            case 1:
                                $groupTwo [] = $key;
                                $countingSteps = $countingSteps + 1;
                                break;
                            case 2:
                                $groupThree [] = $key;
                                $countingSteps = $countingSteps + 1;
                                break;
                            case 3:
                                $groupFour [] = $key;
                                $countingSteps = 0;
                                break;
                        }
                    }
                    
                    
                    for($k = 0; $k <= $groupCount - 1; $k++) {
                        $random = rand(0,1);
                        $first = $random  ? $k : $compInGroup - $k;
                        $second = $random ? $compInGroup - $k : $k;
                        $input['compatition_id'] = $compatition->id;
                        $input['category_id'] = $category_id;
                        $input['pool'] = $j;
                        $input['pool_type'] = $groupType;
                        $input['group'] =  $k + 1;
                        
                        $input['status'] = 0;
                        $input['registration_one'] = $j == 1 ? Arr::get($groupOne, $first . '.id') : null;
                        $input['registration_two'] = $j == 1 ? Arr::get($groupOne, $second .  '.id') : null;
    
                        $input['start_time'] =  $timeTracking;      
                        if($j == 1 && ($input['registration_one'] == null || $input['registration_two'] == null)) {
                            $timeTracking = $timeTracking;
                        } else {
                            $timeTracking = Date("H:i:s", strtotime("$timeTracking + $category_match_lenght minutes"));
                        }                 
                        
                        $singleArr[] = $input;
                    }
                    for($k = 0; $k <= $groupCount - 1; $k++) {
                        $random = rand(0,1);
                        $first = $random  ? $k : $compInGroup - $k;
                        $second = $random ? $compInGroup - $k : $k;
                        $input['compatition_id'] = $compatition->id;
                        $input['category_id'] = $category_id;
                        $input['pool'] = $j;
                        $input['pool_type'] = $groupType;
                        $input['group'] =  $k + $groupCount + 1;
                        
                        $input['status'] = 0;
                        $input['registration_one'] = $j == 1 ? Arr::get($groupThree, $first . '.id') : null;
                        $input['registration_two'] = $j == 1 ? Arr::get($groupThree, $second .  '.id') : null;
    
                        $input['start_time'] =  $timeTracking;    
                        if($j == 1 && ($input['registration_one'] == null || $input['registration_two'] == null)) {
                            $timeTracking = $timeTracking;
                        } else {
                            $timeTracking = Date("H:i:s", strtotime("$timeTracking + $category_match_lenght minutes"));
                        }                   
                        
                        $singleArr[] = $input;
                    }
                    for($k = 0; $k <= $groupCount - 1; $k++) {
                        $random = rand(0,1);
                        $first = $random  ? $k : $compInGroup - $k;
                        $second = $random ? $compInGroup - $k : $k;
                        $input['compatition_id'] = $compatition->id;
                        $input['category_id'] = $category_id;
                        $input['pool'] = $j;
                        $input['pool_type'] = $groupType;
                        $input['group'] =  $k + ($groupCount * 2) + 1;
                        
                        $input['status'] = 0;
                        $input['registration_one'] = $j == 1 ? Arr::get($groupFour, $first . '.id') : null;
                        $input['registration_two'] = $j == 1 ? Arr::get($groupFour, $second .  '.id') : null;
    
                        $input['start_time'] =  $timeTracking;
                        if($j == 1 && ($input['registration_one'] == null || $input['registration_two'] == null)) {
                            $timeTracking = $timeTracking;
                        } else {
                            $timeTracking = Date("H:i:s", strtotime("$timeTracking + $category_match_lenght minutes"));
                        }
                
                        $singleArr[] = $input;
                    }
                    for($k = 0; $k <= $groupCount - 1 ; $k++) {
                        $random = rand(0,1);
                        $first = $random  ? $k : $compInGroup - $k;
                        $second = $random ? $compInGroup - $k : $k;
                        $input['compatition_id'] = $compatition->id;
                        $input['category_id'] = $category_id;
                        $input['pool'] = $j;
                        $input['pool_type'] = $groupType;
                        $input['group'] =  $k + ($groupCount * 3) + 1;
                        
                        $input['status'] = 0;
                        $input['registration_one'] = $j == 1 ? Arr::get($groupTwo, $first . '.id') : null;
                        $input['registration_two'] = $j == 1 ? Arr::get($groupTwo, $second .  '.id') : null;
    
                        $input['start_time'] =  $timeTracking;   
                        if($j == 1 && ($input['registration_one'] == null || $input['registration_two'] == null)) {
                            $timeTracking = $timeTracking;
                        } else {
                            $timeTracking = Date("H:i:s", strtotime("$timeTracking + $category_match_lenght minutes"));
                        }                     
                        
                        $singleArr[] = $input;
                    }
    
                    
                }
                if($startPoint == 0 || $j >= 2) {
                    for($i = 0; $i <= ($counting - 1); $i++) {
                        $random = rand(0,1);
                        $first = $random  ? $i : $neededReg - $i;
                        $second = $random ? $neededReg - $i : $i;
                        $input['compatition_id'] = Arr::get($cleaned, '0.compatition_id');
                        $input['category_id'] = Arr::get($cleaned, '0.category_id');
                        $input['pool'] = $j;
                        $input['pool_type'] = $groupType;
                        $input['group'] =  $i + 1;
                        
                        $input['status'] = 0;
                        $input['registration_one'] = $j == 1 ? Arr::get($cleaned, $first . '.id') : null;
                        $input['registration_two'] = $j == 1 ? Arr::get($cleaned, $second .  '.id') : null;
    
                        $input['start_time'] =  $timeTracking;
                        if($j == 1 && ($input['registration_one'] == null || $input['registration_two'] == null)) {
                            $timeTracking = $timeTracking;
                        } else {
                            $timeTracking = Date("H:i:s", strtotime("$timeTracking + $category_match_lenght minutes"));
                        }
                        
                        
                        $singleArr[] = $input;
                    }
                }
               
                
            }
            
        }

        PoolTeam::insert($teamArr);
        Pool::insert($singleArr);
        return $this->success(['single' => $singleArr, 'teams' => $teamArr]);

    }
    public function rebuildCategoryPool(Request $request) 
    {
        if(!$request->isTeam) {
            foreach($request->groups as $pool) {
                $poolToUpdate = Pool::where('id', $pool['poolId'])->first();
                $poolToUpdate->update([
                    'competitor_one' => $pool['competitorOne'],
                    'competitor_two' => $pool['competitorTwo']
                ]);
            }
            return $this->success('', 'Uspješno izmjenjen žrijeb!');
        }
        if($request->isTeam) {
            foreach($request->groups as $pool) {
                $poolToUpdate = PoolTeam::where('id', $pool['poolId'])->first();
                $poolToUpdate->update([
                    'team_one' => $pool['competitorOne'],
                    'team_two' => $pool['competitorTwo']
                ]);
            }
            return $this->success('', 'Uspješno izmjenjen žrijeb!');
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updatePool(Request $request, Pool $pool)
    {

        $timeTable = TimeTable::where('compatition_id', $pool->compatition_id)->where('category_id', $pool->category_id)->first();
        $category = Category::where('id', $pool->category_id)->first();
        $winnerId = $request->winnerId != 'null' ? $request->winnerId : $pool->winner_id;
        $looserId = $request->looserId != 'null' ? $request->looserId : $pool->looser_id;
        $looserRegistration = Registration::where('id', $looserId)->first();
        $winnerRegistration = Registration::where('id', $winnerId)->first();
        $totalRegistrations = Registration::where('compatition_id', $pool->compatition_id)->where('category_id', $pool->category_id)->get()->count();
        $categoryLenght = $category->match_lenght;


        if($pool->pool == 1 && $pool->group == 1 && $timeTable->status == 0) {
            $nowTime = now();
            $timeTable->update(['status'=> 1, 'started_time' => Date("H:i:s", strtotime("$nowTime - $categoryLenght minutes"))]);
        }
        if($request->has('nextMatchId') && $request->nextMatchId !== 'null') {
            $nextMetch = Pool::where('id', $request->nextMatchId)->first();
            if(!str_contains($nextMetch->pool_type, 'R')){
                $isOdd = $pool->group % 2 == 0 ? 1 : 0;
                $isOdd == 0 ? $nextMetch->update(['registration_one' => $winnerId]) : $nextMetch->update(['registration_two' => $winnerId]);
            } else {
                $request->nextMatchId != 'null' ? $nextMetch->update(['registration_one' => $winnerId]) : null;
            }
        }
        
       
        if($pool->pool_type == 'SF' && $category->repesaz == 0) {
            $request->looserId != 'null' ? $looserResult =  1 : $looserResult = null;
            $request->looserId != 'null' ? $looserRegistration->update(['position' => $looserResult]) : null;
            $winnerRegistration->update(['position' => null]);
            if($totalRegistrations < 4 && $request->looserId == 'null') {
                $winnerRegistration->update(['status' => 0]);
            }
            if($totalRegistrations < 4 && $request->looserId != 'null') {
                $looserRegistration->update(['status' => 0]);
                $winnerRegistration->update(['status' => 1]);
            }
        } 
        if($pool->pool_type == 'FM') {
            $request->looserId != 'null' ? $looserResult =  2 : $looserResult = null;
            $request->winnerId != 'null' ? $winnerResult =  3 : $winnerResult = null;
            $winnerRegistration->update(['position' => $winnerResult]);
            if($totalRegistrations == 1) {
                $winnerRegistration->update(['status' => 0]);
            }
            if($totalRegistrations == 2 ) {
                $winnerRegistration->update(['status' => 1]);
                $looserRegistration->update(['status' => 0]);
            }
            if($totalRegistrations == 3 ) {
                $poolsWinningCount = Pool::where('compatition_id', $pool->compatition_id)->where('category_id', $pool->category_id)->where('winner_id', $request->looserId)->where('looser_id', '!=', null)->where('id', '!=', $pool->id)->count();
                $poolsWinningCount >= 1 ? $looserStatus = 1 : $looserStatus = 0;
                $winnerRegistration->update(['status' => 1]);
                $looserRegistration->update(['status' => $looserStatus]);
            }
            if($category->repesaz == 0) {
                $request->looserId != 'null' ? $looserRegistration->update(['position' => $looserResult]) : null;
                $timeTable->update(['status'=> 2, 'finish_time' => Date("H:i:s", strtotime(now()))]);
            }
        } 
        if($pool->pool_type == 'FM' && $category->repesaz == 1 && $request->winnerId != 'null' && $request->looserId != 'null' ) {
            $getPool = Pool::where('compatition_id', $pool->compatition_id)->where('category_id', $pool->category_id);
            $getLoosers = Pool::where('winner_id', $winnerId)->where('looser_id', '!=', null)->orderBy('pool', 'asc')->get();
            if($getPool->where('pool_type','like',  "%R%")->get()->count() > 0){
                $getPool->where('pool_type','like',  "%R%")->delete();
            }
            $countLoosers = $getLoosers->count();

            $repesazData = [];
            for($i = 2; $i <= $countLoosers; $i++) {
                switch($i) {
                    case $i == $countLoosers -1:
                        $poolType = 'RSF';
                        break;
                    case $i == $countLoosers:
                        $poolType = 'RFM';
                        break;
                    default:
                        $poolType = 'R';
                        break;
                }
                $input['compatition_id'] = $pool->compatition_id;
                $input['category_id'] = $pool->category_id;
                $input['pool'] = $pool->pool + 1;
                $input['pool_type'] = $poolType;
                $input['group'] =  $i - 1;
                
                $input['status'] = 0;
                $firstCompetitor = $i - 2;
                $otherCompetitors = $i - 1;
                
                $lastPoolTime = $pool->start_time;
                $timeTracking = $otherCompetitors * $categoryLenght;
                $input['registration_one'] = $i == 2 ? $getLoosers[$firstCompetitor]->looser_id : null;
                $input['registration_two'] = $getLoosers[$otherCompetitors]->looser_id;
                $input['start_time'] = Date("H:i:s", strtotime("$lastPoolTime + $timeTracking minutes"));
                $repesazData[] = $input;  
            }


          
            Pool::insert($repesazData);
            
            
        }
        if($pool->pool_type == 'RSF') {
            $request->looserId != 'null' ? $looserResult =  1 : $looserResult = null;
            Registration::where('id', $looserId)->first()->update(['position' => $looserResult]);
            Registration::where('id', $winnerId)->first()->update(['position' => null]);
        }
        if($pool->pool_type == 'RFM') {
            $request->looserId != 'null' ? $looserResult =  1 : $looserResult = null;
            $request->winnerId != 'null' ? $winnerResult =  2 : $winnerResult = null;
            Registration::where('id', $winnerId)->first()->update(['position' => $winnerResult ]);
            Registration::where('id', $looserId)->first()->update(['position' => $looserResult]);
            $timeTable->update(['status'=> 2, 'finish_time' => Date("H:i:s", strtotime(now()))]);
        } 
        $request->winnerId != 'null' ? $pool->update(['winner_id' => $winnerId]) : $pool->update(['winner_id' => null]);
        
        $request->looserId != 'null' ? $pool->update(['looser_id' => $looserId]) : $pool->update(['looser_id' => null]);
        return new PoolResource($pool);
    }
    public function updatePoolTeam(Request $request, PoolTeam $poolTeam )
    {
        /**Moram da sredim timove */
        $timeTable = TimeTable::where('compatition_id', $poolTeam->compatition_id)->where('category_id', $poolTeam->category_id)->first();
        $category = Category::where('id', $poolTeam->category_id)->first();
        $winnerId = $request->winnerId != 'null' ? $request->winnerId : $poolTeam->winner_id;
        $looserId = $request->looserId != 'null' ? $request->looserId : $poolTeam->looser_id;
        $winnerRegistration = Registration::where('team_id', $winnerId)->get();
        $looserRegistration = $looserId != 'null' && $looserId != null ? Registration::where('team_id', $looserId)->get() : null;
        $categoryLenght = $category->match_lenght;
        $totalRegistrations = Registration::where('compatition_id', $poolTeam->compatition_id)->where('category_id', $poolTeam->category_id)->get()->groupBy('team_id')->count();
        

        
        if($poolTeam->pool == 1 && $poolTeam->group == 1 && $timeTable->status == 0) {
            $nowTime = now();
            $timeTable->update(['status'=> 1, 'started_time' => Date("H:i:s", strtotime("$nowTime - 10 minutes"))]);
        }

        if($request->has('nextMatchId') && $request->nextMatchId !== 'null') {
            $nextMetch = PoolTeam::where('id', $request->nextMatchId)->first();
            
            if(!str_contains($nextMetch->pool_type, 'R')){
                $isOdd = $poolTeam->group % 2 == 0 ? 1 : 0;
                
                $isOdd == 0 ? $nextMetch->update(['team_one' => $winnerId]) : $nextMetch->update(['team_two' => $winnerId]);
            } else {
                $request->nextMatchId != 'null' ? $nextMetch->update(['team_one' => $winnerId]) : null;
            }
        }

       
        if($poolTeam->pool_type == 'SF' && $category->repesaz == 0) {
            $poolTeam->looser_id != 'null' ? $looserResult =  1 : $looserResult = null;

            if($looserRegistration != null) {
                foreach($looserRegistration as $teamReg) {
                    $teamReg->update(['position' => $looserResult]);
                }
            }
            foreach($winnerRegistration as $teamReg) {
                $teamReg->update(['position' => null]);
            }

            if($totalRegistrations < 4 && $request->looserId == 'null') {
                foreach($winnerRegistration as $teamReg) {
                    $teamReg->update(['status' => 0]);
                }
            }
            if($totalRegistrations < 4 && $request->looserId != 'null') {
                foreach($looserRegistration as $teamReg) {
                    $teamReg->update(['status' => 0]);
                }
                foreach($winnerRegistration as $teamReg) {
                    $teamReg->update(['status' => 1]);
                }
            }

        } 

        if($poolTeam->pool_type == 'FM') {
            $poolTeam->looser_id != 'null' ? $looserResult =  2 : $looserResult = null;
            $poolTeam->winner_id != 'null' ? $winnerResult =  3 : $winnerResult = null;

            $timeTable = TimeTable::where('compatition_id', $poolTeam->compatition_id)->where('category_id', $poolTeam->category_id)->first();
            
            foreach($winnerRegistration as $teamReg) {
                $teamReg->update(['position' => $winnerResult]);
            }

            if($totalRegistrations == 1) {
                foreach($winnerRegistration as $teamReg) {
                    $teamReg->update(['status' => 0]);
                }
            }
            
            if($totalRegistrations == 2) {
                foreach($looserRegistration as $teamReg) {
                    $teamReg->update(['status' => 0]);
                }
            }
            
            if($totalRegistrations == 3) {
                $poolsWinnerCount = PoolTeam::where('compatition_id', $poolTeam->compatition_id)->where('category_id', $poolTeam->category_id)->where('winner_id', $request->looserId)->where('looser_id', '!=', null)->where('id', '!=', $poolTeam->id)->get()->count();
            
                $poolsWinnerCount >= 1 ? $looserStatus = 1 : $looserStatus = 0;
                foreach($winnerRegistration as $teamReg) {
                    $teamReg->update(['status' => 1]);
                }
                foreach($looserRegistration as $teamReg) {
                    $teamReg->update(['status' => $looserStatus]);
                }
            }
            if($category->repesaz == 0 ) {
                if($looserRegistration != null){
                    foreach($looserRegistration as $teamReg) {
                        $teamReg->update(['position' => $looserResult]);
                    }
                }
                $timeTable->update(['status'=> 2, 'finish_time' => Date("H:i:s", strtotime(now()))]);
            }

        } 
        $getPool = PoolTeam::where('compatition_id', $poolTeam->compatition_id)->where('category_id', $poolTeam->category_id);
        if($poolTeam->pool_type == 'FM' && $category->repesaz == 1) {
            
            $getPoolLoosers = $getPool->where('winner_id', $request->winnerId)->where('looser_id', '!=', null)->orderBy('pool', 'asc')->get();
            if($getPool->where('pool_type','like',  "%R%")->get()->count() > 0){
                $getPool->where('pool_type','like',  "%R%")->delete();
            }
            $countLoosers = $getPoolLoosers->count();
            $repesazData = [];
            for($i = 2; $i <= $countLoosers; $i++){
                switch($i) {
                    case $i == $countLoosers -1:
                        $poolType = 'RSF';
                        break;
                    case $i == $countLoosers:
                        $poolType = 'RFM';
                        break;
                    default:
                        $poolType = 'R';
                        break;
                }
                $input['compatition_id'] = $poolTeam->compatition_id;
                $input['category_id'] = $poolTeam->category_id;
                $input['pool'] = $poolTeam->pool + 1;
                $input['pool_type'] = $poolType;
                $input['group'] =  $i - 1;
                
                $input['status'] = 0;
                $firstCompetitor = $i - 2;
                $otherCompetitors = $i - 1;
                
                $lastPoolTime = $poolTeam->start_time;
                $timeTracking = $otherCompetitors * $categoryLenght;
                $input['team_one'] = $i == 2 ? $getPoolLoosers[$firstCompetitor]->looser_id : null;
                $input['team_two'] = $getPoolLoosers[$otherCompetitors]->looser_id;
                $input['start_time'] = Date("H:i:s", strtotime("$lastPoolTime + $timeTracking minutes"));
                $repesazData[] = $input;  
        }

        if($poolTeam->pool_type == 'FM' && $category->repesaz == 1) {
           
            }
            if($poolTeam->pool_type == 'RSF') {
                Registration::where('id', $looserId)->first()->update(['position' => 1]);
                Registration::where('id', $request->winnerId)->first()->update(['position' => null]);
            } 
            if($poolTeam->pool_type == 'RFM') {
                Registration::where('id', $request->winnerId)->first()->update(['position' => 2]);
                Registration::where('id', $looserId)->first()->update(['position' => 1]);
                $timeTable->update(['status'=> 2, 'finish_time' => Date("H:i:s", strtotime(now()))]);
            } 
            
            PoolTeam::insert($repesazData);
        }
        $request->winnerId != 'null' ? $poolTeam->update(['winner_id' => $request->winnerId]) :  $poolTeam->update(['winner_id' => null]);
        $request->looserId != 'null' ? $poolTeam->update(['looser_id' => $request->looserId]) : $poolTeam->update(['looser_id' => null]);
        return new PoolsTeamResource($poolTeam);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
