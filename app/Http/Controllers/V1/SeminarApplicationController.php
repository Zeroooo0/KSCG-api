<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\CompatitorsResource;
use App\Http\Resources\SeminarMorphApplicationResource;
use App\Models\Compatitor;
use App\Models\Seminar;
use App\Models\SeminarMorphApplication;
use App\Models\SpecialPersonal;
use App\Traits\HttpResponses;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SeminarApplicationController extends Controller
{
    use HttpResponses;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, Seminar $seminar)
    {
        $seminarApplications = SeminarMorphApplication::where('seminar_id', $seminar->id);
        return SeminarMorphApplicationResource::collection($seminarApplications->paginate($request->perPage));
    }
    // public function specialPersonnelGet(Request $request, Seminar $seminar)
    // {
    //     $seminarApplications = SeminarMorphApplication::where('seminar_id', $seminar->id);
    //     return SeminarMorphApplicationResource::collection($seminarApplications->paginate($request->perPage));
    // }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, Seminar $seminar)
    {
        $hasCompetitiors = $seminar->has_compatitor;
        $hasCoach = $seminar->has_coach;
        $hasJudge = $seminar->has_judge;
        if($seminar->seminar_type == 'licenceSeminar') {

            //email notification with form data i view blade
            //check does judge and couch if has Form filled up
            //check does judge if has
            
            if(!$request->has('personnelId') && $request->personnelId != null) {
                return $this->error('', 'Potrebno je da odaberete Stručno lice!', 403);
            }
            if(!$hasCompetitiors && $request->has('competitorId')) {
                return $this->error('', 'Ovaj seminar nije za Takmičare!', 403);
            }
           
            $personnel =  SpecialPersonal::where('id', $request->personnelId)->first();
            $isAppliedAlready = $seminar->seminarMorphApplications->where('applicable_type', 'App\Models\SpecialPersonal')->where('applicable_id', $personnel->id);
            $isFormFilledCount = $personnel->specialPersonnelForm->count();
            
            if($isAppliedAlready->count() >= 1) {
                return $this->error('', "$personnel->name $personnel->last_name posjeduje aplikaciju za ovaj seminar!", 401);
            }
            if($isFormFilledCount == 0) {
                return $this->error('', "$personnel->name $personnel->last_name mora da ima ispunjene dodatne podatke", 401);
            }
            if($personnel->role == 2) {
                if(Auth::user()->user_type != 0) {
                    if(!$request->has('clubId')){
                        return $this->error('', 'Potrebno je da odaberete Klub za registraciju trenera!', 403);
                    }
                }
                if(!$hasCoach) {
                    return $this->error('', 'Ovaj seminar nije za trenere!', 403);
                }
                $clubId = $request->has('clubId') ? $request->clubId : Auth::user()->club_id;
                $personnel->seminarMorphApplications()->create([
                    'seminar_id' => $seminar->id,
                    'club_id' => $clubId
                ]);
            }
            if($personnel->role == 1) {
                if(!$hasJudge) {
                    return $this->error('', 'Ovaj seminar nije za Sudije!', 403);
                }
                $personnel->seminarMorphApplications()->create([
                    'seminar_id' => $seminar->id
                ]);
            }
            if($personnel->role == 0) {
                
                return $this->error('', 'Uprava kluba se ne moze prijaviti na Seminar!', 403);
                
            }
            return $this->success('', 'Uspješno kreirana aplikacija!');

        }
        if($seminar->seminar_type == 'educationSeminar') {
            //education seminar for all
        
        
            if($request->has('personnelId')) {
                $personnel =  SpecialPersonal::where('id', $request->personnelId)->first();
                if($personnel->role == 1 && !$hasJudge) {
                    return $this->error('', 'Ovaj seminar nije za Sudije!', 403);
                }
                if($personnel->role == 2 && !$hasCoach) {
                    return $this->error('', 'Ovaj seminar nije za Trenere!', 403);
                }
                $isAppliedAlready = $seminar->seminarMorphApplications->where('applicable_type', 'App\Models\SpecialPersonal')->where('applicable_id', $personnel->id);
                            
                if($isAppliedAlready->count() >= 1) {
                    return $this->error('', "$personnel->name $personnel->last_name posjeduje aplikaciju za ovaj seminar!", 401);
                }
                $personnel->seminarMorphApplications()->create([
                    'seminar_id' => $seminar->id
                ]);
            }
            if($request->has('competitorId')) {
                $competitor =  Compatitor::where('id', $request->competitorId)->first();
                
                $isAppliedAlready = $seminar->seminarMorphApplications->where('applicable_type', 'App\Models\Compatitor')->where('applicable_id', $competitor->id);
                if(!$hasCompetitiors) {
                    return $this->error('', 'Ovaj seminar nije za Takmičare!', 403);
                }
                if($isAppliedAlready->count() >= 1) {
                    return $this->error('', "$competitor->name $competitor->last_name posjeduje aplikaciju za ovaj seminar!", 401);
                }
                $competitor->seminarMorphApplications()->create([
                    'seminar_id' => $seminar->id
                ]);
            }
            return $this->success('', 'Uspješno kreirana aplikacija!');
        }
        
        
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    public function destroy(SeminarMorphApplication $seminarMorphApplication)
    {
        if(Auth::user()->user_type == 0) {
            return $this->error('', 'Ovo je samo dozvloljeno aktivnim administrtorima.', 403);
        }
        if(Auth::user()->user_type != 2 && Auth::user()->status == 0) {
            return $this->error('', 'Ovaj nalog je trenutno suspendovan.', 403);
        }
        if(Auth::user()->user_type != 2 && $seminarMorphApplication->deadline >= new DateTime('now')) {
            return $this->error('', 'Samo administrator moze brisati nakon isteka roka.', 403);
        }
        $seminarMorphApplication->delete();
        return $this->success('', 'Uspjesno obrisana aplikacija.');
    }
}
