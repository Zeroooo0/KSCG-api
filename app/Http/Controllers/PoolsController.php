<?php

namespace App\Http\Controllers;

use App\Http\Resources\PoolResource;
use App\Http\Resources\PoolsTeamResource;
use App\Models\Category;
use App\Models\Compatition;
use App\Models\Pool;
use App\Models\PoolTeam;
use App\Models\Registration;
use App\Models\Team;
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
    public function automatedStore(Request $request)
    {
        if(Auth::user()->user_type == 0 || Auth::user()->user_type == 1 && Auth::user()->status == 0 ) {
            return $this->error('', 'Not allowed!', 403);
        }
        $compatition = Compatition::where('id', $request->compatitionId)->get()->first();
        $registrations = $compatition->registrations;
        $reg_single = $registrations->where('team_or_single', 1)->countBy('category_id');
        $reg_teams = $registrations->where('team_or_single', 0)->countBy('category_id');
        $pools = $compatition->pools;
        $timeTable = $compatition->timeTable;
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

        /*
        if($pools->where('compatition_id', $request->compatitionId)->count() > 0) {
            return $this->error('', 'Žrijebanje je već odrađeno za ovo takmičenje', 403);
        }
        if($timeTable->count() == 0) {
            return $this->error('', 'Potrebno je prvo da se odredi Time Table', 422);
        }
        */
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
        $pool->update(['winner_id' => $request->winnerId]);
        $request->looserId != 'null' ? $pool->update(['looser_id' => $request->looserId]) : null;
        if($request->has('nextMatchId')) {
            $nextMetch = Pool::where('id', $request->nextMatchId)->first();
            $isOdd = $pool->group % 2 == 0 ? 1 : 0;
 
            $isOdd == 0 ? $nextMetch->update(['registration_one' => $request->winnerId]) : $nextMetch->update(['registration_two' => $request->winnerId]);
        }
        $category = Category::where('id', $pool->category_id)->first();
       
        if($pool->pool_type == 'SF' && $category->repesaz == 0) {
            Registration::where('id', $request->looserId)->first()->update(['position' => 1]);
            Registration::where('id', $request->winnerId)->first()->update(['position' => NULL]);
        } 
        if($pool->pool_type == 'FM' && $category->repesaz == 0) {
            Registration::where('id', $request->winnerId)->first()->update(['position' => 3]);
            Registration::where('id', $request->looserId)->first()->update(['position' => 2]);
        } 

        return new PoolResource($pool);
    }
    public function updatePoolTeam(Request $request, PoolTeam $poolTeam )
    {
        $poolTeam->update(['winner_id' => $request->winnerId]);
        $request->looserId != 'null' ? $poolTeam->update(['looser_id' => $request->looserId]) : null;
        if($request->has('nextMatchId')) {
            $nextMetch = PoolTeam::where('id', $request->nextMatchId)->first();
            $isOdd = $poolTeam->group % 2 == 0 ? 1 : 0;
 
            $isOdd == 0 ? $nextMetch->update(['team_one' => $request->winnerId]) : $nextMetch->update(['team_two' => $request->winnerId]);
        }

        $category = Category::where('id', $poolTeam->category_id)->first();
       
        if($poolTeam->pool_type == 'SF' && $category->repesaz == 0) {
            $teamLooser = Team::where('id', $request->looserId)->first()->registrations;
            $teamWinner = Team::where('id', $request->looserId)->first()->registrations;
            foreach($teamLooser as $teamReg) {
                $teamReg->update(['position' => 1]);
            }
            foreach($teamWinner as $teamReg) {
                return $teamReg;
                $teamReg->update(['position' => NULL]);
            }
            
        } 
        if($poolTeam->pool_type == 'FM' && $category->repesaz == 0) {
            $teamLoose = Team::where('id', $request->winnerId)->first()->registrations;
            $teamWin = Team::where('id', $request->win)->first()->registrations;
            foreach($teamLoose as $teamReg) {
                $teamLoose->update(['position' => 2]);
            }
            foreach($teamWin as $teamReg) {
                $teamWin->update(['position' => 3]);
            }
        } 
        if($poolTeam->pool_type == 'FM' && $category->repesaz == 1) {
            //
        }
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
