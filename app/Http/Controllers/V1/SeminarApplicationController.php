<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\SeminarFormApplicationResource;
use App\Http\Resources\SeminarMorphApplicationResource;
use App\Http\Resources\SeminarResource;
use App\Models\Compatitor;
use App\Models\Roles;
use App\Models\Seminar;
use App\Models\SeminarFormApplication;
use App\Models\SeminarMorphApplication;
use App\Models\SpecialPersonal;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

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
        $seminarApplications = SeminarFormApplication::where('seminar_id', $seminar->id);
        return SeminarFormApplicationResource::collection($seminarApplications->paginate($request->perPage));
    }
    public function indexMorph(Request $request, Seminar $seminar)
    {
        $seminarApplications = SeminarMorphApplication::where('seminar_id', $seminar->id);
        return SeminarMorphApplicationResource::collection($seminarApplications->paginate($request->perPage));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, Seminar $seminar)
    {
        if($seminar->seminar_type == 'licenceSeminar') {
            $request->validate([
                'formType' => 'required|string|in:judge,coach',
                'fullName' => 'required|string|max:191',
                'nameOfParent' => 'required|string|max:191',
                'jmbg' => 'required|numeric|digits_between:13,13',
                'birthDate' => 'required|date',
                'birthPlace' => 'required|string|max:191',
                'address' => 'required|string|max:191',
                'landlinePhone' => 'string|max:191',
                'mobPhone' => 'required|string|max:191',
                'email'=> 'required|email|max:191',
                'belt' => 'required|string|max:191',
                'beltAcquired' => 'required|date',
                'certificate' => 'required|string|max:191',
                'certificateId' => 'required|string|max:191',
                'certificateAcquired' => 'required|date',
                'certificateIssuer' => 'required|string|max:191',
                'policyConfirmation'=> 'required|boolean',
            ]);
            if($request->formType == 'judge') {
                $request->validate([
                    'judgeTitle' => 'required|string|max:191',
                    'judgeTitleAcquired' => 'required|date'
                ]);
                $application = SeminarFormApplication::create([
                    'seminar_id' => $seminar->id,
                    'form_type' => $request->formType,
                    'full_name' => $request->fullName,
                    'name_of_parent' => $request->nameOfParent,
                    'jmbg' => $request->jmbg,
                    'birth_date' => $request->birthDate,
                    'birth_place' => $request->birthPlace,
                    'address' => $request->address,
                    'landline_phone' => $request->landlinePhone,
                    'mob_phone' => $request->mobPhone,
                    'email'=> $request->email,
                    'belt' => $request->belt,
                    'belt_acquired' => $request->beltAcquired,
                    'certificate' => $request->certificate,
                    'certificate_id' => $request->certificateId,
                    'certificate_acquired' => $request->certificateAcquired,
                    'certificate_issuer' => $request->certificateIssuer,
                    'policy_confirmation'=> $request->policyConfirmation,
                    'judge_title' => $request->judgeTitle,
                    'judge_title_acquired' => $request->judgeTitleAcquired
                ]);
            }
            if($request->formType == 'coach') {
                $request->validate([
                    'clubApplyingFor' => 'required|string|max:191',
                    'clubLastSeason' => 'required|string|max:191',
                    'forCategories' => 'required|string|max:191',
                ]);
                $application = SeminarFormApplication::create([
                    'seminar_id' => $seminar->id,
                    'form_type' => $request->formType,
                    'full_name' => $request->fullName,
                    'name_of_parent' => $request->nameOfParent,
                    'jmbg' => $request->jmbg,
                    'birth_date' => $request->birthDate,
                    'birth_place' => $request->birthPlace,
                    'address' => $request->address,
                    'landline_phone' => $request->landlinePhone,
                    'mob_phone' => $request->mobPhone,
                    'email'=> $request->email,
                    'belt' => $request->belt,
                    'belt_acquired' => $request->beltAcquired,
                    'certificate' => $request->certificate,
                    'certificate_id' => $request->certificateId,
                    'certificate_acquired' => $request->certificateAcquired,
                    'certificate_issuer' => $request->certificateIssuer,
                    'policy_confirmation'=> $request->policyConfirmation,
                    'club_applying_for' => $request->clubApplyingFor,
                    'club_last_season' => $request->clubLastSeason,
                    'for_categories' => $request->forCategories,
                ]);
            }
            //email notification with form data i view blade
            return $this->success(new SeminarFormApplicationResource($application), 'Uspješno kreirana aplikacija!');
        }
        if($seminar->seminar_type == 'educationSeminar') {
            if($request->formType == 'judge') {
                $request->validate([
                    'formType' => 'required|string|in:judge,coach',
                    'fullName' => 'required|string|max:191',
                    'nameOfParent' => 'required|string|max:191',
                    'jmbg' => 'required|numeric|digits_between:13,13',
                    'birthDate' => 'required|date',
                    'birthPlace' => 'required|string|max:191',
                    'address' => 'required|string|max:191',
                    'landlinePhone' => 'string|max:191',
                    'mobPhone' => 'required|string|max:191',
                    'email'=> 'required|email|max:191',
                    'belt' => 'required|string|max:191',
                    'beltAcquired' => 'required|date',
                    'certificate' => 'required|string|max:191',
                    'certificateId' => 'required|string|max:191',
                    'certificateAcquired' => 'required|date',
                    'certificateIssuer' => 'required|string|max:191',
                    'policyConfirmation'=> 'required|boolean',
                    'judgeTitle' => 'required|string|max:191',
                    'judgeTitleAcquired' => 'required|date'
                ]);
                $application = SeminarFormApplication::create([
                    'seminar_id' => $seminar->id,
                    'form_type' => $request->formType,
                    'full_name' => $request->fullName,
                    'name_of_parent' => $request->nameOfParent,
                    'jmbg' => $request->jmbg,
                    'birth_date' => $request->birthDate,
                    'birth_place' => $request->birthPlace,
                    'address' => $request->address,
                    'landline_phone' => $request->landlinePhone,
                    'mob_phone' => $request->mobPhone,
                    'email'=> $request->email,
                    'belt' => $request->belt,
                    'belt_acquired' => $request->beltAcquired,
                    'certificate' => $request->certificate,
                    'certificate_id' => $request->certificateId,
                    'certificate_acquired' => $request->certificateAcquired,
                    'certificate_issuer' => $request->certificateIssuer,
                    'policy_confirmation'=> $request->policyConfirmation,
                    'judge_title' => $request->judgeTitle,
                    'judge_title_acquired' => $request->judgeTitleAcquired
                ]);
            }
            if($request->has('competitorId') && $request->has('personnelId')) {
                return $this->error('', 'Mozete uneti samo takmicara ili trenera!', 403);
            }
            if($request->has('competitorId')) {
                $request->validate([
                    'competitorId' => 'required|integer|exists:compatitors,id',
                ]);
                $doesHaveApplications = $seminar->seminarMorphApplications->where('applicable_type', 'App\Models\Compatitor')->where('applicable_id', $request->competitorId)->first();
                if($doesHaveApplications->count() > 0) {
                    return $this->error('', 'Ovaj takmičar je već prijavljen na seminar!', 401);
                }
                $competitor = Compatitor::where('id', $request->competitorId)->first();
                $competitor->seminarMorphApplications()->create([
                    'seminar_id' => $seminar->id,
                    'club_id' => $competitor->club->id
                ]);
                return $this->success('', 'Uspješno kreirana prijava za seminar!');
            }

            if($request->has('personnelId')) {
                $request->validate([
                    'personnelId' => 'required|integer|exists:special_personals,id',
                ]);
                $doesHaveApplications = $seminar->seminarMorphApplications->where('applicable_type', 'App\Models\SpecialPersonal')->where('applicable_id', $request->personnelId)->first();
                if($doesHaveApplications->count() > 0) {
                    return $this->error('', 'Ovaj trener je već prijavljen na seminar!', 401);
                }
                $personnel = SpecialPersonal::where('id', $request->personnelId)->first();
                $role = Roles::where('special_personals_id', $request->personnelId)->where('roleable_type', 'App\Models\Club')->first();
                if($role == null){
                    return $this->error('', 'Ovaj trener nije prijavljen u klub!', 403);
                };
                $personnel->seminarMorphApplications()->create([
                    'seminar_id' => $seminar->id,
                    'club_id' => $role->roleable_id
                ]);
                return $this->success('','Uspješno kreirana prijava za seminar!');
            }
        }
        
        
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, SeminarFormApplication $seminarFormApplication)
    {
        if(Auth::user()->user_type == 0) {
            return $this->error('', 'Ovo je samo dozvloljeno aktivnim administrtorima.', 403);
        }
        if(Auth::user()->user_type != 2 && Auth::user()->status == 0) {
            return $this->error('', 'Ovaj nalog je trenutno suspendovan.', 403);
        }
        $request->validate([
            'personnelId' => 'integer|exists:special_personals,id',
            'status' => 'boolean'
        ]);
        if($request->has('personnelId')) {
            $seminarFormApplication->update([
                'personnel_id' => $request->personnelId
            ]);
        }
        if($request->has('status')) {
            if($seminarFormApplication->personnel_id == null){ 
                return $this->error('', 'Morate povezati formu sa odgovarajucom osobom u sistemu!', 403);
            }
            $seminarFormApplication->update([
                'status' => $request->status
            ]);
            $seminarFormApplication->specialPersonal->update(['status' => $request->status]);
        }
        return $this->success('', 'uspješno dopunjena form');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(SeminarFormApplication $seminarFormApplication)
    {
        if(Auth::user()->user_type == 0) {
            return $this->error('', 'Ovo je samo dozvloljeno aktivnim administrtorima.', 403);
        }
        if(Auth::user()->user_type != 2 && Auth::user()->status == 0) {
            return $this->error('', 'Ovaj nalog je trenutno suspendovan.', 403);
        }
        $seminarFormApplication->delete();
        return $this->success('', 'Uspjesno obrisana aplikacija.');
    }
    public function destroyMorph(SeminarMorphApplication $seminarMorphApplication)
    {
        if(Auth::user()->user_type == 0) {
            return $this->error('', 'Ovo je samo dozvloljeno aktivnim administrtorima.', 403);
        }
        if(Auth::user()->user_type != 2 && Auth::user()->status == 0) {
            return $this->error('', 'Ovaj nalog je trenutno suspendovan.', 403);
        }
        $seminarMorphApplication->delete();
        return $this->success('', 'Uspjesno obrisana aplikacija.');
    }
}
