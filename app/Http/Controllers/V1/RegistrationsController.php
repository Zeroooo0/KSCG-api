<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\RegistrationsResource;
use App\Models\Compatition;
use App\Models\Compatitor;
use App\Models\Registration;
use App\Traits\HttpResponses;
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
    public function index(Request $request, Compatition $competition)
    {
        $per_page = $request->perPage;
        $competitionId = $competition->id;

        if(Auth::user() != null){
            if(Auth::user()->user_type == 0) {
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
        $applicationLimit = $competition->application_limits;
        $category = $competition->categories->where('id',$request->categoryId)->first();
        $isItSingle = $category->solo_or_team;
        $isItKata = $category->kata_or_kumite;
        $dateFrom = $category->date_from;
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
            $olderCategoryError = false;
            $genderError = false;
            if($isItSingle && $registrations->where('compatitor_id',$competitor->id)->count() >= $applicationLimit) {
                $isItError = true;
            }
            if($registrations->where('compatitor_id', $competitor->id)->where('category_id', $category->id)->count() >= 1) {
                $categoryError = true;
            }
            if($isItKata && $competitor->date_of_birth < $dateFrom && $competitor->belt->id < 7 ) {
                $olderCategoryError = true;
            }
            if($category->gender != 3 && $category->gender != $competitor->gender) {
                $genderError = true;
            }
         
            //return $competitor->belt  ;

            if($isItError == false && $categoryError == false && $olderCategoryError = false && $genderError = false) {
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
            if ($olderCategoryError == true) {
                $name = $competitor->name;
                $lastName = $competitor->last_name;
                $input['message'] = "Samo takmičar $name $lastName u apsolutnom nivou se samo moze prijaviti u starijem godištu!";
                $input['competitorId'] = (string)$competitor->id;
                $responseErrorMessage[] = $input;
            }
            if ($genderError == true) {
                $name = $competitor->name;
                $lastName = $competitor->last_name;
                $input['message'] = "Pol takmičara $name $lastName nije adekvatan za ovu kategoriju!";
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
        return $this->success('', 'Uspješno obrisana registracija!');
    }
}
