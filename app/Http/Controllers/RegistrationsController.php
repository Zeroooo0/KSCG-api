<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRegistrationRequest;
use App\Http\Resources\CompatitorsResource;
use App\Http\Resources\RegistrationsResource;
use App\Models\Category;
use App\Models\Club;
use App\Models\Compatition;
use App\Models\Compatitor;
use App\Models\Registration;
use App\Models\SpecialPersonal;
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
    public function index(Request $request, Compatition $compatition)
    {
        $per_page = $request->perPage;
        $competitionId = $compatition->id;
    
        if(Auth::user() != null){
            if(Auth::user()->user_type == 0 && Auth::user()->club != null) {
                $clubId = Auth::user()->club->id;
                return RegistrationsResource::collection(Registration::where('compatition_id', $competitionId)->where('club_id', $clubId)->paginate($per_page));
            }

            if(Auth::user()->user_type == 0 && Auth::user()->club == null){
                return $this->error('', 'Molimo vas da prvo kreirate klub!',403);
            }
            if(Auth::user()->user_type != 0) {                   
                return RegistrationsResource::collection(Registration::where('compatition_id', $competitionId)->paginate($per_page));
            }
        } 
        if(Auth::user() == null) {
            return RegistrationsResource::collection(Registration::where('compatition_id', $competitionId)->paginate($per_page));
        }
      
       
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function newStore(Request $request, Compatition $competition) 
    {
        $category = $competition->categories->where('id',$request->categoryId)->first();
        $isItSingle = $category->solo_or_team;
        $isItKata = $category->kata_or_kumite;
        $competitorsIds = $request->competitors;
        $competitiors = Compatitor::whereIn('id',$competitorsIds)->get();
        $registrations = $competition->registrations->where('team_or_single', $isItSingle)->where('kata_or_kumite', $isItKata);
        $arrayOfRegistrations = [];
        $responseErrorMessage = [];
        if(!$isItSingle) {
            $team = $competition->teams()->create([
                'name' => $request->teamName
            ]);
        }
        
  
        foreach($competitiors as $competitor) {
            $isItError = false;
            $categoryError = false;
            if($isItSingle && $registrations->where('compatitor_id',$competitor->id)->count() >= 2) {
                $isItError = true;
            }
            if($registrations->where('compatitor_id', $competitor->id)->where('category_id', $category->id)->count() >= 1) {
                $categoryError = true;
            }

            if($isItError == false && $categoryError == false) {
                $input['compatition_id'] = $competition->id;
                $input['club_id'] = $competitor->club_id != null ? $competitor->club->id : null;
                $input['compatitor_id'] = $competitor->id;
                $input['category_id'] = $category->id;
                $input['team_id'] = $isItSingle ? null : $team->id;
                $input['team_or_single'] = $category->solo_or_team;
                $input['kata_or_kumite'] = $category->kata_or_kumite;
                $input['created_at'] = date("Y:m:d H:i:s");
                $input['updated_at'] = date("Y:m:d H:i:s");
                $input['status'] = 1;
                $arrayOfRegistrations[] = $input;
            } 
            if ($isItError == true) {
                $limitedCount = $isItSingle ? '2 prijave' : '1 prijavu';
                $singleOrTeam = $isItSingle ? 'pojedinčnom' :'timskom';
                $kateOrKumite = $isItKata ? 'kate' : 'kumite';
                $name = $competitor->name;
                $lastName = $competitor->last_name;
                $input['message'] = "Takmičar $name $lastName ima $limitedCount u $singleOrTeam nastupu $kateOrKumite!";
                $input['competitorId'] = (string)$competitor->id;
                $responseErrorMessage[] = $input;
            }
            if ($categoryError == true) {
                $name = $competitor->name;
                $lastName = $competitor->last_name;
                $input['message'] = "Takmičar $name $lastName je već prijavljen u ovoj kategoriji!";
                $input['competitorId'] = (string)$competitor->id;
                $responseErrorMessage[] = $input;
            }
        }
        if(count($responseErrorMessage) == 0) {
            Registration::insert($arrayOfRegistrations);
            return $this->success('', 'Registracija uspješna!');
        }
        return $this->error($responseErrorMessage, 'Provjerite podatke!', 403);
    }
    public function store(StoreRegistrationRequest $request)
    {
        /*
        if(Auth::user()->user_type != 2 && Auth::user()->status == 0) {
            return $this->restricted('', 'Vaš nalog nije aktivan, kontaktirajte Karate Savez!', 403);
        }
        */
        $arr = [];
        $compatitionCheck = Compatition::where('id', $request[0]['competitionId'])->get()->first();
        if($compatitionCheck->registration_status == 1){
            if($compatitionCheck->registration_deadline <= date(now())) {
                $compatitionCheck->update(['registration_status' => false]);
                return $this->error('', 'Registracije su završene', 403);
            }
        }
        if($compatitionCheck->registration_status == 0) {
            return $this->error('', 'Registracije su završene', 403);
        }

        foreach($request->all() as $key) {
            $data = $key;
            $compatition = Compatition::where('id', $data['competitionId'])->get()->first();
            $clubId = Compatitor::where('id', $data['competitorId'])->first()->club_id;
            $club = Club::where('id', $clubId)->first();
            if($club->status == 0) {
                return $this->error('', 'Vaš klub je trenutno neaktivan, pokušajte kasnije!', 403);
            }
            
            
            $categories = $compatition->categories->where('id', $data['categoryId'])->first();
            if($categories == null){
                return $this->error('', 'Odabrana je nepostojeća kategorija na ovom takmičenju!', 403);
            }
            $team_or_solo = $categories->solo_or_team;
            $kata_or_kumite = $categories->kata_or_kumite;           
            
            if($team_or_solo == 0 && $data['teamId'] == null){
                return $this->error('', 'Ekipne kategorije moraju da posjeduju id tima!', 403);
            }
            if($team_or_solo == 1 && $data['teamId'] != null){
                $data['teamId'] == null;
            }

            $input['compatition_id'] = $data['competitionId'];
            $input['club_id'] = $clubId;
            $input['compatitor_id'] = $data['competitorId'];
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
            $compatition = Compatition::where('id', $data['competitionId'])->get()->first();
            $team_or_solo = $compatition->categories->where('id', $data['categoryId'])->first()->solo_or_team;
            $kata_or_kumite = $compatition->categories->where('id', $data['categoryId'])->first()->kata_or_kumite;


            //rekonstruisan objekat dolaznih podataka
            $incoming_data_counter = $arr_collection->where('compatition_id', $data['competitionId'])->where('compatitor_id', $data['competitorId'])->where('team_or_single', $team_or_solo)->where('kata_or_kumite', $kata_or_kumite);
            //postojeci podaci u DB
            //return response($data['category_id']);
            $existing_data_counter = $compatition->registrations->where('compatitor_id', $data['competitorId'])->where('team_or_single', $team_or_solo)->where('kata_or_kumite',  $kata_or_kumite);
            //kategorije dolazni podaci
            $incoming_category_count = $arr_collection->where('compatition_id', $data['competitionId'])->where('compatitor_id', $data['competitorId'])->where('category_id', $data['categoryId']);
            //kategorije postojeci podaci u DB
            $existing_category_count = $compatition->registrations->where('compatition_id', $data['competitionId'])->where('category_id', $data['categoryId'])->where('compatitor_id', $data['competitorId']);
            
            $some_count = $existing_data_counter->last() == null ? 0 : $existing_data_counter->count();

            if($some_count + $incoming_data_counter->count() > 2) {
                return $this->error('','Takmičar ' . Compatitor::where('id', $data['competitorId'])->first()->name . ' ' . Compatitor::where('id', $data['competitorId'])->first()->last_name . ' ' . Compatitor::where('id', $data['competitorId'])->first()->id . ' ovom prijavom krši takmičarski pravilnik za prijave!', 403);
            }
            if($incoming_category_count->count() > 1) {
                return $this->error('', 'Takmičar ' . Compatitor::where('id', $incoming_category_count->first()['compatitor_id'])->first()->name . ' ' . Compatitor::where('id', $incoming_category_count->first()['compatitor_id'])->first()->last_name . ' pokusavate da ubacite u istu kategoriju!', 403);
            }
            if($existing_category_count->count() >= 1 ) {
                return $this->error('', 'Takmičar ' . Compatitor::where('id', $existing_category_count->first()['compatitor_id'])->first()->name . ' ' . Compatitor::where('id', $existing_category_count->first()['compatitor_id'])->first()->last_name . ' pokusavate da ubacite u istu kategoriju!', 403);
            }
            $input['compatition_id'] = $data['competitionId'];
            $input['club_id'] = Compatitor::where('id', $data['competitorId'])->first()->club_id;
            $input['compatitor_id'] = $data['competitorId'];
            $input['category_id'] = $data['categoryId'];
            $input['team_id'] = $team_or_solo == 1 ? null : $data['teamId'];
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
            return $this->success($finish_arr, 'Uspješna registracija takmičara!');
        }
        return $this->error($exit_obj, 'poslati su podaci nepostojećih takmičara ili', 402);

    }

     /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Registration $registration)
    {
        //
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
