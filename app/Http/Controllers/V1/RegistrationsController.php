<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\RegistrationsResource;
use App\Models\Category;
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
            if($competition->registration_deadline >= now()){
                $competition->update(['registration_status' => 0]);
            }
            if(Auth::user()->user_type == 0) {
                $clubId = Auth::user()->club->id;
                return RegistrationsResource::collection(Registration::where('compatition_id', $competitionId)->where('club_id', $clubId)->paginate($per_page));
            }
            if(Auth::user()->user_type != 0 && $request->has('clubId')) {   
                $clubId = $request->clubId;
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
            if($competition->registration_deadline >= now()){
                $competition->update(['registration_status' => 0]);
            }
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
        $isItMale = $category->gender == 1;
        $isItFemale = $category->gender == 2;
        $dateTo = $category->date_to;
        $competitorsIds = $request->competitors;
        $competitiors = Compatitor::whereIn('id',$competitorsIds)->get();
        $registrations = $competition->registrations->where('team_or_single', $isItSingle)->where('kata_or_kumite', $isItKata);
        $arrayOfRegistrations = [];
        $responseErrorMessage = [];
        if($competition->registration_status == 0) {
            $this->error('', 'Prijave su trenutno onemogućene ili su istekle!', 403);
        }
        if(!$isItSingle) {
            $team = $competition->teams()->create([
                'name' => $request->teamName
            ]);
        }
        if(!$isItSingle && !$isItKata && ($isItMale || $isItFemale) && ($competitiors->count() < 3 || $competitiors->count() > 4)) {
            $team ['message'] =  "Nema dovoljno takmičara u ekipi, minimum 3 a maksimum 4 takmičara!";
            $responseErrorMessage [] =  $team;
        }
        if(!$isItSingle && $isItKata && $isItMale && ($competitiors->count() < 5 || $competitiors->count() > 6)) {
            $team ['message'] =  "Nema dovoljno takmičara u ekipi minimum 3 a maksimum 4 takmičara!";
            $responseErrorMessage [] =  $team;
        }
        foreach($competitiors as $competitor) {
            $isItError = false;
            $categoryError = false;
            $olderCategoryError = false;
            $genderError = false;
            $beltError = false;
            $generationError = false;


            if($isItSingle && $registrations->where('compatitor_id',$competitor->id)->where('kata_or_kumite', $isItKata)->count() >= $applicationLimit) {
                $isItError = true;
            }
            if($registrations->where('compatitor_id', $competitor->id)->where('category_id', $category->id)->count() >= 1) {
                $categoryError = true;
            }
            if($isItSingle && $isItKata && $competitor->date_of_birth > $dateTo && $competitor->belt->id < 7 ) {
                $olderCategoryError = true;
            }
            if($category->gender != 3 && $category->gender != $competitor->gender) {
                $genderError = true;
            }
          
            if($isItSingle && $isItKata && !$category->belts->isEmpty()) {
                $corrector = false;
                foreach($category->belts as $belt) {
                    if($belt->id == $competitor->belt_id) {
                        $corrector = true;
                    }
                }
                if($corrector == false) {
                    $beltError = true;
                }
            }
            if($isItSingle && ($isItKata || !$isItKata) && $competitor->date_of_birth > $dateTo && $applicationLimit == 1) {
                $generationError = true;
            }


            if(!$isItError && !$categoryError && !$olderCategoryError && !$genderError && !$beltError && !$generationError) {
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
            if ($isItError) {
                $limitedCount = $isItSingle ? '2 prijave' : '1 prijavu';
                $singleOrTeam = $isItSingle ? 'pojedinčnom' :'timskom';
                $kateOrKumite = $isItKata ? 'kate' : 'kumite';
                $name = $competitor->name;
                $lastName = $competitor->last_name;
                $input['message'] = "Takmičar $name $lastName ima $limitedCount u $singleOrTeam nastupu $kateOrKumite!";
                $input['competitorId'] = (string)$competitor->id;
                $responseErrorMessage[] = $input;
            }
            if ($categoryError) {
                $name = $competitor->name;
                $lastName = $competitor->last_name;
                $input['message'] = "Takmičar $name $lastName je već prijavljen u ovoj kategoriji!";
                $input['competitorId'] = (string)$competitor->id;
                $responseErrorMessage[] = $input;
            }
            if ($olderCategoryError) {
                $name = $competitor->name;
                $lastName = $competitor->last_name;
                $input['message'] = "Takmičar $name $lastName nije u apsolutnom nivou pa se ne moze prijaviti u starijem godištu!";
                $input['competitorId'] = (string)$competitor->id;
                $responseErrorMessage[] = $input;
            }
            if ($genderError) {
                $name = $competitor->name;
                $lastName = $competitor->last_name;
                $input['message'] = "Pol takmičara $name $lastName nije adekvatan za ovu kategoriju!";
                $input['competitorId'] = (string)$competitor->id;
                $responseErrorMessage[] = $input;
            }
            if ($beltError) {
                $name = $competitor->name;
                $lastName = $competitor->last_name;
                $input['message'] = "Takmičara $name $lastName nema adekvatan pojas za ovu kategoriju!";
                $input['competitorId'] = (string)$competitor->id;
                $responseErrorMessage[] = $input;
            }
            if ($generationError) {
                $name = $competitor->name;
                $lastName = $competitor->last_name;
                $input['message'] = "Takmičara $name $lastName moze da se prijavi samo u svom godištu!";
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
        $category = Category::where('id', $registration->category_id)->first();
        if($registration->solo_or_team == 0 ) {
            $teamId = $registration->team_id;
            $categoryGender = $category->gender;
            $teamDelete = Registration::where('team_id', $teamId)->get();
            if($category->kata_or_kumite == 0 && $categoryGender == 1 && $teamDelete->count() - 1 < 5) {
                foreach($teamDelete as $teamMember) {
                    $teamMember->delete();
                }
                return $this->success('', 'Uspješno obrisana ekipa!');
            }
            if($teamDelete->count() - 1 < 3) {
                
                foreach($teamDelete as $teamMember) {
                    $teamMember->delete();
                }
                return $this->success('', 'Uspješno obrisana ekipa!');
            }
            if($teamDelete->count() - 1 < 3) {
                foreach($teamDelete as $teamMember) {
                    $teamMember->delete();
                }
                return $this->success('', 'Uspješno obrisana ekipa!');
            }
        }
        $registration->delete();
        return $this->success('', 'Uspješno obrisana registracija!');
    }
}
