<?php

namespace App\Http\Controllers;

use App\Http\Resources\PoolResource;
use App\Models\Category;
use App\Models\Compatition;
use App\Models\Pool;
use App\Models\PoolTeam;
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
            $category_timeStart = $timeTable->where('category_id', $category_id)->first()->eto_start;
            $category = Category::where('id', $category_id)->first();
            $category_match_lenght = $category->mathc_lenght;
            $catSpec = $this->categoryDuration($compatition, $category);
            $count = $catSpec['categoryGroupsBack'];
            $pool = $catSpec['categoryPoolsBack'];

            for($i = 0; $i <= $count; $i++) {
                $first = $i;
                $second = ($count * 2 + 1) - $i;
                $inputTeam['compatition_id'] = Arr::get($val, '0.0.compatition_id');
                $inputTeam['category_id'] = Arr::get($val, '0.0.category_id');
                $inputTeam['pool'] = $pool;
                $inputTeam['pool_type'] = 'P';
                $inputTeam['group'] = $i;
                $inputTeam['start_time'] = $i == 0 ? $category_timeStart : $category_timeStart + $i * $category_match_lenght;
                $inputTeam['status'] = false;
                $inputTeam['team_one'] = Arr::get($val,  $first . '.0.team_id');
                $inputTeam['team_two'] = Arr::get($val, $second .  '.0.team_id');
                $teamArr[] = $inputTeam;
            }
        }
   
        foreach($nn_single_cat as $val) {
            $category_id =  $val[0]->category_id;
            $category_timeStart = $timeTable->where('category_id', $category_id)->first()->eto_start;
            $category = Category::where('id', $category_id)->first();
            $category_match_lenght = $category->mathc_lenght;
            $catSpec = $this->categoryDuration($compatition, $category);
            $count = $catSpec['categoryGroupsBack'];
            $pool = $catSpec['categoryPoolsBack'];
            
            $cat_ids = $val->groupBy('club_id')->sortDesc();
            $sorted_cats = [];
            foreach($cat_ids as $item=>$key) {
                $sorted_cats[] = $key;
            }
            $cleaned = Arr::collapse($sorted_cats);
            for($j = 0; $j <= $pool; $j++) {
                $counting = $count;
                for($i = 0; $i <= $count; $i++) {
                    $first = 0 + $i;
                    $second = ($count * 2 + 1) - $i;
                    $input['compatition_id'] = Arr::get($cleaned, '0.compatition_id');
                    $input['category_id'] = Arr::get($cleaned, '0.category_id');
                    $input['pool'] = $pool;
                    $input['pool_type'] = 'G';
                    $input['group'] =  $j * $i;
                    $inputTeam['start_time'] = $i == 0 ? $category_timeStart : $category_timeStart + $i * $category_match_lenght;
                    $input['status'] = false;
                    $input['registration_one'] = Arr::get($cleaned, $first . '.id');
                    $input['registration_two'] = Arr::get($cleaned, $second .  '.id');
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
    public function update(Request $request, $compatition)
    {
        $compatition = Compatition::where('id', $compatition)->first();
        $pools = $compatition->pools->where('category_id', $request->categoryId);
        $request_data = collect($request->all())->values();
        foreach($request_data as $data) {
            return response($pools->where('id', $data['id'])->first()->update(['pool' => 2]));
            $pools->where('id', $data['id'])->update($data);
            
        }

        return $this->success('', $pools);

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
