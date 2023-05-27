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
  
        //STAR category change
        if(isset($request->categoryId) && $requestedCategory->solo_or_team == 1) {
            $pool = $pools->where('category_id', $request->categoryId);
            $data = collect($request)->except(['compatitionId', 'categoryId']);
            if($pool->count() == 0) {
                return $this->error('', 'Prvo odradite žrijebanje da bi ste mogli da editujete!', 403);
            }

            foreach($data as $new_data) {
                $input['compatition_id'] = $request->compatitionId;
                $input['category_id'] = $request->categoryId;
                $input['pool'] = $new_data['pool'];
                $input['pool_type'] = $new_data['poolType'];
                $input['group'] = $new_data['group'];
                $input['status'] = $new_data['status'];
                $input['registration_one'] = $new_data['registrationOne'];
                $input['registration_two'] = $new_data['registrationTwo'];
                $singleArr[] = $input;
            }
            if($pools->where('category_id', $request->categoryId)->count() > 0) {
                foreach($pool as $trash) {
                    $trash->delete();
                }
            }
            //return $singleArr;
            Pool::insert($singleArr);
            return $this->success($singleArr);
        }



        if(isset($request->categoryId) && $requestedCategory->solo_or_team == 0) {
            $pool = $pools->where('category_id', $request->categoryId);
            $data = collect($request)->except(['compatitionId', 'categoryId']);
            if($pool->count() == 0) {
                return $this->error('', 'Prvo odradite žrijebanje da bi ste mogli da editujete!', 403);
            }

            foreach($data as $new_data) {
                $input['compatition_id'] = $request->compatitionId;
                $input['category_id'] = $request->categoryId;
                $input['pool'] = $new_data['pool'];
                $input['pool_type'] = $new_data['poolType'];
                $input['group'] = $new_data['group'];
                $input['status'] = $new_data['status'];
                $input['team_one'] = $new_data['registrationOne'];
                $input['team_two'] = $new_data['registrationTwo'];
                $teamArr[] = $input;
            }
            if($pools->where('category_id', $request->categoryId)->count() > 0) {
                foreach($pool as $trash) {
                    $trash->delete();
                }
            }

            PoolTeam::insert($singleArr);
            return $this->success($singleArr);
        }
        //END category change

    
        if($pools->where('compatition_id', $request->compatitionId)->count() > 0) {
            return $this->error('', 'Žrijebanje je već odrađeno za ovo takmičenje', 403);
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
            $catSpec = $this->categoryDuration($compatition, $category);
            $count = $catSpec['categoryGroupsFront'];
            $pool = $catSpec['categoryPoolsFront'];
            $timeTracking = $category_timeStart;

            for($j = 1; $j <= $pool; $j++) {
                $counting = $count;
                switch($j) {
                    case $j == 1:
                        $counting = $count;
                        break;
                    case $j == 2:
                        $counting = $count / 2;
                        break;
                    case $j == 3:
                        $counting = $count / 4;
                        break;
                    case $j == 4:
                        $counting = $count / 8;
                        break;
                    case $j == 5:
                        $counting = $count / 16;
                        break;
                    case $j == 6:
                        $counting = $count  / 32;
                        break;
                    case $j == 7:
                        $counting = $count  / 64;
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
            
                for($i = 0; $i <= ($counting - 1); $i++) {
                    $random = rand(0,1);
                    $first = $random  ? $i : ($counting * 2 - 1) - $i;
                    $second = $random ? ($counting * 2 - 1) - $i : $i;
                    $inputTeam['compatition_id'] = Arr::get($val, '0.0.compatition_id');
                    $inputTeam['category_id'] = Arr::get($val, '0.0.category_id');
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
            $catSpec = $this->categoryDuration($compatition, $category);
            $count = $catSpec['categoryGroupsFront'];
            $pool = $catSpec['categoryPoolsFront'];
            $cat_ids = $val->groupBy('club_id')->sortDesc();
            $sorted_cats = [];
            foreach($cat_ids as $item=>$key) {
                $sorted_cats[] = $key;
            }
            
            $cleaned = Arr::collapse($sorted_cats);
            $timeTracking = $category_timeStart;
            for($j = 1; $j <= $pool; $j++) {
                $counting = $count;
                switch($j) {
                    case $j == 1:
                        $counting = $count;
                        break;
                    case $j == 2:
                        $counting = $count / 2;
                        break;
                    case $j == 3:
                        $counting = $count / 4;
                        break;
                    case $j == 4:
                        $counting = $count / 8;
                        break;
                    case $j == 5:
                        $counting = $count / 16;
                        break;
                    case $j == 6:
                        $counting = $count  / 32;
                        break;
                    case $j == 7:
                        $counting = $count  / 64;
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
                for($i = 0; $i <= ($counting - 1); $i++) {
                    $random = rand(0,1);
                    $first = $random  ? $i : ($counting * 2 - 1) - $i;
                    $second = $random ? ($counting * 2 - 1) - $i : $i;
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

        PoolTeam::insert($teamArr);
        Pool::insert($singleArr);
        return $this->success(['single' => $singleArr, 'teams' => $teamArr]);

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
            $looserRegistration->update(['position' => $looserResult]);
            $winnerRegistration->update(['position' => null]);
        } 
        if($pool->pool_type == 'FM') {
            $request->looserId != 'null' ? $looserResult =  2 : $looserResult = null;
            $request->winnerId != 'null' ? $winnerResult =  3 : $winnerResult = null;
            Registration::where('id', $winnerId)->first()->update(['position' => $winnerResult]);
            if($category->repesaz == 0) {
                Registration::where('id', $looserId)->first()->update(['position' => $looserResult]);
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
        $looserRegistration = Registration::where('team_id', $looserId)->get();
        $categoryLenght = $category->match_lenght;

        if($poolTeam->pool == 1 && $poolTeam->group == 1 && $timeTable->status == 0) {
            $nowTime = now();
            $timeTable->update(['status'=> 1, 'started_time' => Date("H:i:s", strtotime("$nowTime - 10 minutes"))]);
        }

        if($request->has('nextMatchId') && $request->nextMatchId !== 'null') {
            $nextMetch = Pool::where('id', $request->nextMatchId)->first();
            if(!str_contains($nextMetch->pool_type, 'R')){
                $isOdd = $poolTeam->group % 2 == 0 ? 1 : 0;
                $isOdd == 0 ? $nextMetch->update(['registration_one' => $winnerId]) : $nextMetch->update(['registration_two' => $winnerId]);
            } else {
                $request->nextMatchId != 'null' ? $nextMetch->update(['registration_one' => $winnerId]) : null;
            }
        }

       
        if($poolTeam->pool_type == 'SF' && $category->repesaz == 0) {
            $poolTeam->looser_id != 'null' ? $looserResult =  1 : $looserResult = null;
            $teamLooser = Team::where('id', $looserId)->first()->registrations;
            $teamWinner = Team::where('id', $winnerId)->first()->registrations;
            foreach($teamLooser as $teamReg) {
                $teamReg->update(['position' => $looserResult]);
            }
            foreach($teamWinner as $teamReg) {
                $teamReg->update(['position' => null]);
            }
        } 
        if($poolTeam->pool_type == 'FM') {
            $poolTeam->looser_id != 'null' ? $looserResult =  2 : $looserResult = null;
            $poolTeam->winner_id != 'null' ? $winnerResult =  3 : $winnerResult = null;
            $teamLoose = Team::where('id', $request->winnerId)->first()->registrations;
            $teamWin = Team::where('id', $request->win)->first()->registrations;
            $timeTable = TimeTable::where('compatition_id', $poolTeam->compatition_id)->where('category_id', $poolTeam->category_id)->first();

            foreach($teamWin as $teamReg) {
                $teamWin->update(['position' => $winnerResult]);
            }
            if($category->repesaz == 0) {
                foreach($teamLoose as $teamReg) {
                    $teamLoose->update(['position' => $looserResult]);
                }
                $timeTable->update(['status'=> 2, 'finish_time' => Date("H:i:s", strtotime(now()))]);
            }
        } 
        if($poolTeam->pool_type == 'FM' && $category->repesaz == 1) {
            $getPool = PoolTeam::where('compatition_id', $poolTeam->compatition_id)->where('category_id', $poolTeam->category_id);
            $getLoosers = PoolTeam::where('winner_id', $request->winnerId)->where('looser_id', '!=', null)->orderBy('pool', 'asc')->get();
            if($getPool->where('pool_type','like',  "%R%")->get()->count() > 0){
                $getPool->where('pool_type','like',  "%R%")->delete();
            }
            $countLoosers = $getLoosers->count();
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
                $input['team_one'] = $i == 2 ? $getLoosers[$firstCompetitor]->looser_id : null;
                $input['team_two'] = $getLoosers[$otherCompetitors]->looser_id;
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
