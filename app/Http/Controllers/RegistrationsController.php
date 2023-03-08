<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRegistrationRequest;
use App\Http\Resources\RegistrationsResource;
use App\Models\Category;
use App\Models\Compatition;
use App\Models\Compatitor;
use App\Models\Registration;
use App\Traits\HttpResponses;
use ArrayObject;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RegistrationsController extends Controller
{
    use HttpResponses;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $per_page = $request->perPage;
        if(Auth::user()->user_type != 0 || Auth::user()->user_type == 1 && Auth::user()->status == 1){
            return RegistrationsResource::collection(Registration::paginate($per_page));
        }
        if(Auth::user()->user_type == 0 && Auth::user()->status == 1) {
            return RegistrationsResource::collection(Registration::where('club_id', Auth::user()->club_id)->paginate($per_page));
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreRegistrationRequest $request)
    {
        if(Auth::user()->user_type != 2 && Auth::user()->status == 0) {
            return $this->restricted('', 'Not alowed!', 403);
        }
        
        $arr = [];

       
        foreach($request->all() as $key) {
            $data = $key;
            $compatition = Compatition::where('id', $data['compatitionId'])->get()->first();
            
            if( $compatition->registration_status == 0) {
                return $this->error('', 'Registracija je trenutno neaktivna!', 403);
            }
            
            
            $categories = $compatition->categories->where('id', $data['categoryId'])->first();
            if($categories == null){
                return $this->error('', 'Odabrana je ne postojeća kategorija na ovom takmičenju!', 403);
            }
            $team_or_solo = $categories->solo_or_team;
            $kata_or_kumite = $categories->kata_or_kumite;           
            
            if($team_or_solo == 0 && $data['teamId'] == null){
                return $this->error('', 'Ekipne kategorije moraju da posjeduju id tima!', 403);
            }
            if($team_or_solo == 1 && $data['teamId'] != null){
                $data['teamId'] == null;
            }

            $input['compatition_id'] = $data['compatitionId'];
            $input['club_id'] = Compatitor::where('id', $data['compatitorId'])->first()->club_id;
            $input['compatitor_id'] = $data['compatitorId'];
            $input['category_id'] = $data['categoryId'];
            $input['team_id'] = $data['teamId'] == null ? null : $data['teamId'];
            $input['team_or_single'] = $team_or_solo;
            $input['kata_or_kumite'] = $kata_or_kumite;
            $input['created_at'] = date("Y:m:d H:i:s");
            $input['updated_at'] = date("Y:m:d H:i:s");
            $input['status'] = 1;
            $arr[] = $input;

        }
        $incoming_obj = new ArrayObject($request->all());
        
        $arr_collection = collect($arr);
        $finish_arr = [];
        foreach($request->all() as $val) {
            $data = $val;
            $compatition = Compatition::where('id', $data['compatitionId'])->get()->first();
            $team_or_solo = $compatition->categories->where('id', $data['categoryId'])->first()->solo_or_team;
            $kata_or_kumite = $compatition->categories->where('id', $data['categoryId'])->first()->kata_or_kumite;

            //return response()->json($arr_collection);
            //rekonstruisan objekat dolaznih podataka
            $incoming_data_counter = $arr_collection->where('compatition_id', $data['compatitionId'])->where('compatitor_id', $data['compatitorId'])->where('team_or_single', $team_or_solo)->where('kata_or_kumite', $kata_or_kumite);
            //postojeci podaci u DB
            //return response($data['category_id']);
            $existing_data_counter = $compatition->registrations->where('compatitor_id', $data['compatitorId'])->where('team_or_single', $team_or_solo)->where('kata_or_kumite',  $kata_or_kumite);
            //kategorije dolazni podaci
            $incoming_category_count = $arr_collection->where('compatition_id', $data['compatitionId'])->where('compatitor_id', $data['compatitorId'])->where('category_id', $data['categoryId']);
            //kategorije postojeci podaci u DB
            $existing_category_count = $compatition->registrations->where('compatition_id', $data['compatitionId'])->where('category_id', $data['categoryId'])->where('compatitor_id', $data['compatitorId']);
            
            $some_count = $existing_data_counter->last() == null ? 0 : $existing_data_counter->count();
            
        


            if($some_count + $incoming_data_counter->count() > 2) {
                return $this->error('','Takmičar ' . Compatitor::where('id', $data['compatitorId'])->first()->name . ' ' . Compatitor::where('id', $data['compatitorId'])->first()->last_name . ' ovom prijavom krši takmičarski pravilnik za prijave!', 403);
            }
            if($incoming_category_count->count() > 1) {
                return $this->error('', 'Takmičar ' . Compatitor::where('id', $incoming_category_count->first()['compatitor_id'])->first()->name . ' ' . Compatitor::where('id', $incoming_category_count->first()['compatitor_id'])->first()->last_name . ' pokusavate da ubacite u istu kategoriju!', 403);
            }
            if($existing_category_count->count() >= 1 ) {
                return $this->error('', 'Takmičar ' . Compatitor::where('id', $existing_category_count->first()['compatitor_id'])->first()->name . ' ' . Compatitor::where('id', $existing_category_count->first()['compatitor_id'])->first()->last_name . ' pokusavate da ubacite u istu kategoriju!', 403);
            }
            $input['compatition_id'] = $data['compatitionId'];
            $input['club_id'] = Compatitor::where('id', $data['compatitorId'])->first()->club_id;
            $input['compatitor_id'] = $data['compatitorId'];
            $input['category_id'] = $data['categoryId'];
            $input['team_id'] = $data['teamId'] == null ? null : $data['teamId'];
            $input['team_or_single'] = $team_or_solo;
            $input['kata_or_kumite'] = $kata_or_kumite;
            $input['created_at'] = date("Y:m:d H:i:s");
            $input['updated_at'] = date("Y:m:d H:i:s");
            $input['status'] = 1;
            $finish_arr[] = $input;
        }    

        $exit_obj = new ArrayObject($finish_arr);
        if($incoming_obj->count() == $exit_obj->count()) {
            Registration::insert($finish_arr);
            return $this->success('', $finish_arr);
        }

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Registration $registration)
    {
        $registration->delete();
    }
}
