<?php

namespace App\Http\Controllers;

use App\Http\Resources\PoolResource;
use App\Models\Category;
use App\Models\Compatition;
use App\Models\Pool;
use App\Models\Registration;
use App\Traits\HttpResponses;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;

class PoolsController extends Controller
{
    use HttpResponses;
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
    public function store(Request $request)
    {
        if(Auth::user()->user_type == 0 || Auth::user()->user_type == 1 && Auth::user()->status == 0 ) {
            return $this->error('', 'Not allowed!', 403);
        }
        $compatition = Compatition::where('id', $request->compatitionId)->get()->first();
        $registrations = $compatition->registrations;
        $reg_count = $registrations->countBy('category_id');
        $pools = $compatition->pools;
        $nn_single_cat = [];
        $nn_team_cat = [];

        foreach($reg_count as $key=>$count){
            $nn_single_cat[] = $registrations->sortBy('club_id')->where('category_id', $key)->where('team_or_single', 1)->values();
            $nn_team_cat[] = $registrations->where('category_id', $key)->where('team_or_single', 0)->sortBy('club_id')->values();
        }

        $arr = [];
        if(isset($request->categoryId)) {
            $pool = $pools->where('category_id', $request->categoryId);
            $data = collect($request)->except(['compatitionId', 'categoryId']);
            if($pools->where('category_id', $request->categoryId)->count() == 0) {
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
                $arr[] = $input;
            }
            if($pools->where('category_id', $request->categoryId)->count() > 0) {
                foreach($pool as $trash) {
                    $trash->delete();
                }
            }

            Pool::insert($arr);
            
            return $this->success('', $arr);
        }
        if($pools->where('compatition_id', $request->compatitionId)->count() > 0) {
            return $this->error('', 'Žrijebanje je već odrađeno za ovo takmičenje', 403);
        }
        foreach($nn_single_cat as $val) {
            $count = 0;
   
            switch (count($val)){
                case count($val) <= 4:
                    $count = 1;
                    break;
                case count($val) > 4 && $count <= 8:
                    $count = 3;
                    break;
                case count($val) > 8 && $count <= 16:
                    $count = 7;
                    break;
                case count($val) > 16 && $count <= 32:
                    $count = 15;
                    break;
                case count($val) > 32 && $count <= 64:
                    $count = 31;
                    break;
            }
  
            for($i = 0; $i <= $count; $i++) {
                $first = 0 + $i;
                $second = ($count * 2 + 1) - $i;

                $input['compatition_id'] = Arr::get($val, '0.compatition_id');
                $input['category_id'] = Arr::get($val, '0.category_id');
                $input['pool'] = 1;
                $input['pool_type'] = 'P';
                $input['group'] = $i;
                $input['status'] = false;
                $input['registration_one'] = Arr::get($val, $first . '.id');
                $input['registration_two'] = Arr::get($val, $second .  '.id');
                $arr[] = $input;
                
            }
        }
        Pool::insert($arr);
        return $this->success('', $arr);

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
