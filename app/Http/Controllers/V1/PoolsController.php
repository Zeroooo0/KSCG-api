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
use App\Traits\CompatitionClubsResultsTrait;
use App\Traits\HttpResponses;
use App\Traits\LenghtOfCategory;
use App\Traits\PoolsGroups;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;

class PoolsController extends Controller
{
    use HttpResponses;
    use PoolsGroups;
    use CompatitionClubsResultsTrait;
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
        foreach($compatition->registrations as $reg) {
            $reg->update(['position'=> null]);
        }

        if($compatition->registrations->where('position', '!=', NULL)->count() > 0) {
            return $this->error('', 'Takmicenje je vec pocelo', 422);
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
            $catTimeStart = $timeTableData->eto_start == null ? null : $timeTableData->eto_start;
            $category = Category::where('id', $category_id)->first();

            $registrations = $compatition->registrations->where('category_id', $category->id);
            $timePerCategory = $category->match_lenght;
            $registrationCount = $registrations->groupBy('team_id')->count();
            switch($registrationCount) {
                case $registrationCount <= 2:
                    $groupsReal = 1;
                    break;
                case $registrationCount <= 4:
                    $groupsReal = 2;
                    break;
                case $registrationCount <= 8:
                    $groupsReal = 4;
                    break;
                case $registrationCount <= 16:
                    $groupsReal = 8;
                    break;
                case $registrationCount <= 32:
                    $groupsReal = 16;
                    break;
                case $registrationCount <= 64:
                    $groupsReal = 32;
                    break;
                case $registrationCount <= 128:
                    $groupsReal = 64;
                    break;
            }

            $teamArr[] = $this->newSortGroups($groupsReal, $val, $timePerCategory, $catTimeStart, 'team', $registrationCount);

        }

        foreach($nn_single_cat as $val) {
            $category_id =  $val[0]->category_id;
            $timeTableData = $timeTable->where('category_id', $category_id)->first();
            $catTimeStart = $timeTableData->eto_start == null ? null : $timeTableData->eto_start;
            $category = Category::where('id', $category_id)->first();
            $cat_ids = $val->groupBy('club_id')->sortDesc();

            $cleaned = Arr::collapse($cat_ids);

            $registrations = $compatition->registrations->where('category_id', $category->id);
            $timePerCategory = $category->match_lenght;
            $registrationCount = $registrations->count();

            switch($registrationCount) {
                case $registrationCount <= 2:
                    $groupsReal = 1;
                    break;
                case $registrationCount <= 4:
                    $groupsReal = 2;
                    break;
                case $registrationCount <= 8:
                    $groupsReal = 4;
                    break;
                case $registrationCount <= 16:
                    $groupsReal = 8;
                    break;
                case $registrationCount <= 32:
                    $groupsReal = 16;
                    break;
                case $registrationCount <= 64:
                    $groupsReal = 32;
                    break;
                case $registrationCount <= 128:
                    $groupsReal = 64;
                    break;
            }
            $singleArr[] = $this->newSortGroups($groupsReal, $cleaned, $timePerCategory, $catTimeStart, 'single', $registrationCount );          
                    
        }
        //return Arr::collapse($teamArr);
        PoolTeam::insert(Arr::collapse($teamArr));
        Pool::insert(Arr::collapse($singleArr));
        return $this->success(['single' => Arr::collapse($singleArr), 'teams' => Arr::collapse($teamArr)]);

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
        $compatition = Compatition::where('id', $pool->compatition_id)->first();
        $clubsArray = [];
        $looserRegistration ? $clubsArray[] = $looserRegistration->club_id : null;
        $winnerRegistration ? $clubsArray[] = $winnerRegistration->club_id : null;

        
        $request?->kataOne ? $pool->update(['kata_one_id' => $request->kataOne]) : $pool->update(['kata_one_id' => null]);
        $request?->kataTwo ? $pool->update(['kata_two_id' => $request->kataTwo]) : $pool->update(['kata_two_id' => null]);
    
        if($pool->pool == 1 && $pool->group == 1 && $timeTable->status == 0) {
            $nowTime = now();
            $timeTable->update(['status'=> 1, 'started_time' => Date("H:i:s", strtotime("$nowTime - $categoryLenght minutes"))]);
        }
        if($request->has('nextMatchId') && $request->nextMatchId !== 'null') {
            $nextMetch = Pool::where('id', $request->nextMatchId)->first();
            if($nextMetch != '' && !str_contains($nextMetch->pool_type, 'R')){
                $isOdd = $pool->group % 2 == 0 ? 1 : 0;
                $isOdd == 0 ? $nextMetch->update(['registration_one' => $winnerId]) : $nextMetch->update(['registration_two' => $winnerId]);
            } else {
                $nextMetch != '' && $request->nextMatchId != 'null' ? $nextMetch->update(['registration_one' => $winnerId]) : null;
            }
        }
        //return response()->json(!in_array($pool->pool_type, ['FM', 'SF', 'RFM', 'RSF']));
        if(!in_array($pool->pool_type, ['FM', 'SF', 'REFM'])) {
            $request->looserId != 'null' ? $looserRegistration->update(['position' => null]) : null;
            $winnerRegistration->update(['position' => null]);
        }
        
        if($pool->pool_type == 'SF' && $compatition->rematch == 0) {
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
            $request->looserId != 'null' ? $looserRegistration->update(['position' => $looserResult]) : null;
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
            if($compatition->rematch == 0) {
                $timeTable->update(['status'=> 2, 'finish_time' => Date("H:i:s", strtotime(now()))]);
            }
            if($compatition->rematch == 1) {
                if($category->repesaz == 0 || ($category->repesaz == 1 && $category->kata_or_kumite == 1)) {
                    $timeTable->update(['status'=> 2, 'finish_time' => Date("H:i:s", strtotime(now()))]);
                } 
                if($category->repesaz == 1 && $category->kata_or_kumite == 0) {
                    $request->winnerId != 'null' ? $pool->update(['winner_id' => $winnerId]) : $pool->update(['winner_id' => null]);
                    $request->looserId != 'null' ? $pool->update(['looser_id' => $looserId]) : $pool->update(['looser_id' => null]);
                    $repesazData = $this->rematchBuilding($pool);
                    $oldRepData = Pool::where('compatition_id', $pool->compatition_id)->where('category_id', $pool->category_id)->whereIn('pool_type', ['RE', 'REFM']);
                    if($oldRepData->get()->count() > 0){
                        $oldRepData->delete();
                    }
                    Pool::insert($repesazData);
                }
            }
        }
        
        //Repasaz
        if($pool->pool_type == 'RE') {
            $request->nextMatchId != 'null' ? $nextMetch->update(['registration_one' => $winnerId]) : null;
        }
        if($pool->pool_type == 'REFM'){
            $request->winnerId != 'null' ? $winnerRegistration->update(['position' => '1']) : null;
            $request->looserId != 'null' ? $looserRegistration->update(['position' => '0.9']) : null;
            $timeTable->update(['status'=> 2, 'finish_time' => Date("H:i:s", strtotime(now()))]);
        }
        // if($pool->pool_type == 'FM' && $compatition->rematch == 1) {
        //     if($category->repesaz == 1)
        // }
        // if($pool->pool_type == 'FM' && $compatition->rematch == 1 && $category->repesaz == 1 ) {
        //     $getPool = Pool::where('compatition_id', $pool->compatition_id)->where('category_id', $pool->category_id);
        //     $getLoosers = $getPool->where('winner_id', $winnerId)->where('looser_id', '!=', null)->where('looser_id', '!=', $request->looserId)->orderBy('pool', 'asc')->get();
        //     if($getPool->where('pool_type','like',  "%R%")->get()->count() > 0){
        //         $getPool->where('pool_type','like',  "%R%")->delete();
        //     }
        //     $countLoosers = $getLoosers->count();

        //     $repesazData = [];
        //     for($i = 1; $i <= $countLoosers; $i++) {
        //         switch($i) {
        //             case $i == $countLoosers:
        //                 $poolType = 'RFM';
        //                 break;
        //             default:
        //                 $poolType = 'R';
        //                 break;
        //         }
        //         $input['compatition_id'] = $pool->compatition_id;
        //         $input['category_id'] = $pool->category_id;
        //         $input['pool'] = $pool->pool + 1;
        //         $input['pool_type'] = $poolType;
        //         $input['group'] =  $i - 1;
                
        //         $input['status'] = 0;
        //         $firstCompetitor = $i - 2;
        //         $otherCompetitors = $i - 1;
                
        //         $lastPoolTime = $pool->start_time;
        //         $timeTracking = $otherCompetitors * $categoryLenght;
        //         $input['registration_one'] = $i == 2 ? $getLoosers[$firstCompetitor]->looser_id : null;
        //         $input['registration_two'] = $getLoosers[$otherCompetitors]->looser_id;
        //         $input['start_time'] = Date("H:i:s", strtotime("$lastPoolTime + $timeTracking minutes"));
        //         $repesazData[] = $input;  
        //     }


          
        //     Pool::insert($repesazData);
            
            
        // }
        // if($pool->pool_type == 'RR') {
        //     $request->winnerId != 'null' ? $pool->update(['winner_id' => $winnerId]) : $pool->update(['winner_id' => null]);
        //     $request->looserId != 'null' ? $pool->update(['looser_id' => $looserId]) : $pool->update(['looser_id' => null]);
        //     $poolsRR = Pool::where('compatition_id', $pool->compatition_id)->where('category_id',$pool->category_id)->where('pool_type', 'RR')->get();
        //     $poolsRRFM = Pool::where('compatition_id', $pool->compatition_id)->where('category_id',$pool->category_id)->where('pool_type', 'RRFM')->first();
        //     $groupOne = [1,2,3];
        //     $groupTwo = [4,5,6];
        //     $totalRRPools = $poolsRR->count();

        //     $totalFinishedGroupOne = $poolsRR->whereIn('group', $groupOne)->where('winner_id', '!=', 'null')->count();
        //     $totalFinishedGroupTwo = $poolsRR->whereIn('group', $groupTwo)->where('winner_id', '!=', 'null')->count();
        //     $registeredRR = Registration::where('compatition_id', $pool->compatition_id)->where('category_id', $pool->category_id)->get();
        //     if($totalRRPools == 3 && $totalFinishedGroupOne == 3) {
        //         //$registeredRR = Registration::where('compatition_id', $pool->compatition_id)->where('category_id', $pool->category_id)->get();
                

        //     }
        //     if($totalRRPools == 6 ) {
        //         if($pool->group <= 3 && $totalFinishedGroupOne == 3) {
        //             $firstPlaceRRGroupOne = null;
        //             $secondPlaceRRGroupOne = null;
        //             $thirdPlaceRRGroupOne = null;

        //             foreach($registeredRR as $regCompa) {
        //                 $winingCount = $poolsRR->whereIn('group', $groupOne)->where('winner_id', $regCompa->id)->count();
        //                 $loosingCount = $poolsRR->whereIn('group', $groupOne)->where('looser_id', $regCompa->id)->count();

        //                 $loosingCount == 2 ? $thirdPlaceRRGroupOne = $regCompa->id : null;
        //                 switch($winingCount){
        //                     case 2:
        //                         $firstPlaceRRGroupOne = $regCompa->id;
        //                         break;
        //                     case 1:
        //                         $secondPlaceRRGroupOne = $regCompa->id;
        //                         break;
        //                 }
        //             }
        //             $registrationsPositionCount = Registration::whereIn('id', [$firstPlaceRRGroupOne, $secondPlaceRRGroupOne, $thirdPlaceRRGroupOne])->get();
        //             if($firstPlaceRRGroupOne != null) {
        //                 $poolsRRFM->update(['registration_one' => $firstPlaceRRGroupOne]);
        //             }
        //             if($secondPlaceRRGroupOne != null) {
        //                 $registrationUpdate = Registration::where('id', $secondPlaceRRGroupOne)->first();
        //                 $registrationUpdate->update(['position' => '1']);
        //             }
        //             return $registrationUpdate;
        //         }
        //         if($pool->group > 3 && $totalFinishedGroupTwo == 3) {
        //             $firstPlaceRRGroupTwo = null;
        //             $secondPlaceRRGroupTwo = null;
        //             $thirdPlaceRRGroupTwo = null;


        //             foreach($registeredRR as $regCompa) {
        //                 $winingCount = $poolsRR->whereIn('group', $groupTwo)->where('winner_id', $regCompa->id)->count();
        //                 $loosingCount = $poolsRR->whereIn('group', $groupTwo)->where('looser_id', $regCompa->id)->count();
        //                 $loosingCount == 2 ? $thirdPlaceRRGroupTwo = $regCompa->id : null;

        //                 switch($winingCount){
        //                     case 2:
        //                         $firstPlaceRRGroupTwo = $regCompa->id;
        //                         break;
        //                     case 1:
        //                         $secondPlaceRRGroupTwo = $regCompa->id;
        //                         break;
        //                 }
        //             }
        //             $registrationsPositionCount = Registration::whereIn('id', [$firstPlaceRRGroupTwo, $secondPlaceRRGroupTwo, $thirdPlaceRRGroupTwo])->where('position', '!=', 'null')->get();
        //             return $registrationsPositionCount->count();
        //             if($firstPlaceRRGroupTwo != null) {
        //                 $poolsRRFM->update(['registration_two' => $firstPlaceRRGroupTwo]);
        //             }
        //             if($secondPlaceRRGroupTwo != null) {
        //                 $registrationUpdate = Registration::where('id', $secondPlaceRRGroupTwo)->first();
        //                 $registrationUpdate->update(['position' => '1']);
        //             }
        //             return $registrationUpdate;
        //         }
                
        //         return "$registeredRR 111";

        //     }
        //     return 1;
        //     if($totalRRPools == 5) {
        //         //
        //     }

        // }
        // if($pool->pool_type == 'REFM') {
        //     $request->looserId != 'null' ? $looserResult =  1 : $looserResult = null;
        //     Registration::where('id', $looserId)->first()->update(['position' => $looserResult]);
        //     Registration::where('id', $winnerId)->first()->update(['position' => null]);
        // }
        // if($pool->pool_type == 'RFM') {
        //     $request->looserId != 'null' ? $looserResult =  1 : $looserResult = null;
        //     $request->winnerId != 'null' ? $winnerResult =  2 : $winnerResult = null;
        //     Registration::where('id', $winnerId)->first()->update(['position' => $winnerResult ]);
        //     Registration::where('id', $looserId)->first()->update(['position' => $looserResult]);
        //     $timeTable->update(['status'=> 2, 'finish_time' => Date("H:i:s", strtotime(now()))]);
        // } 
        if($pool->pool_type != 'RR') {
            $request->winnerId != 'null' ? $pool->update(['winner_id' => $winnerId]) : $pool->update(['winner_id' => null]);
            $request->looserId != 'null' ? $pool->update(['looser_id' => $looserId]) : $pool->update(['looser_id' => null]);
        }
        //update results
        $this->calculateResults($pool->compatition_id ,array_unique($clubsArray), 'results');
    
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
        $compatition = Compatition::where('id', $poolTeam->compatition_id)->first();
        $categoryLenght = $category->match_lenght;
        $totalRegistrations = Registration::where('compatition_id', $poolTeam->compatition_id)->where('category_id', $poolTeam->category_id)->get()->groupBy('team_id')->count();
        
        $clubsArray = [];
        
        $looserRegistration ? $clubsArray[] = $looserRegistration->first()->club_id : null;
        $winnerRegistration ? $clubsArray[] = $winnerRegistration->first()->club_id : null;
        
        
        if($poolTeam->pool == 1 && $poolTeam->group == 1 && $timeTable->status == 0) {
            $nowTime = now();
            $timeTable->update(['status'=> 1, 'started_time' => Date("H:i:s", strtotime("$nowTime - 10 minutes"))]);
        }

        if($request->has('nextMatchId') && $request->nextMatchId !== 'null') {
            $nextMetch = PoolTeam::where('id', $request->nextMatchId)->first();
            
            if($nextMetch != null && !str_contains($nextMetch->pool_type, 'R')){
                $isOdd = $poolTeam->group % 2 == 0 ? 1 : 0;
                
                $isOdd == 0 ? $nextMetch->update(['team_one' => $winnerId]) : $nextMetch->update(['team_two' => $winnerId]);
            } else {
                $request->nextMatchId != null ? $nextMetch->update(['team_one' => $winnerId]) : null;
            }
        }
        
        if(!in_array($poolTeam->pool_type, ['FM', 'SF', 'RFM', 'RSF'])) {
            if($request->looserId != 'null') {
                foreach($looserRegistration as $teamRegOne) {
                    $teamRegOne->update(['position' => null]);
                }
            }
            foreach($winnerRegistration as $teamRegtwo) {
                $teamRegtwo->update(['position' => null]);
            }
        }

       
        if($poolTeam->pool_type == 'SF' && $compatition->rematch == 0 ) {
            $poolTeam->looser_id != 'null' ? $looserResult =  1 : $looserResult = null;

            if($request->looserId != 'null') {
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
                if($looserRegistration->count() != 0)
                {
                    foreach($looserRegistration as $teamReg) {
                        $teamReg->update(['status' => 0]);
                    }
                }
            }
            
            if($totalRegistrations == 3) {
                $poolsWinnerCount = PoolTeam::where('compatition_id', $poolTeam->compatition_id)->where('category_id', $poolTeam->category_id)->where('winner_id', $request->looserId)->where('looser_id', '!=', null)->where('id', '!=', $poolTeam->id)->get()->count();
            
                $poolsWinnerCount >= 1 ? $looserStatus = 1 : $looserStatus = 0;
                foreach($winnerRegistration as $teamReg) {
                    $teamReg->update(['status' => 1]);
                }
                if($looserRegistration != null){
                    foreach($looserRegistration as $teamReg) {
                        $teamReg->update(['status' => $looserStatus]);
                    }
                }

            }
            if($compatition->rematch == 0 ) {
                if($request->looserId != 'null'){
                    foreach($looserRegistration as $teamReg) {
                        $teamReg->update(['position' => $looserResult]);
                    }
                }
                $timeTable->update(['status'=> 2, 'finish_time' => Date("H:i:s", strtotime(now()))]);
            }

        } 
        $getPool = PoolTeam::where('compatition_id', $poolTeam->compatition_id)->where('category_id', $poolTeam->category_id);
        if($poolTeam->pool_type == 'FM' && $compatition->rematch == 1 && $category->repesaz == 1) {
            
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

        if($poolTeam->pool_type == 'FM' && $compatition->rematch && $category->repesaz == 1) {
           
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
                //update results

        $this->calculateResults($poolTeam->compatition_id, array_unique($clubsArray), 'results');
   
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
