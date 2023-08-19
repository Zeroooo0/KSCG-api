<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\SpecialPersonalsResource;
use App\Http\Resources\SpecialPersonnelFormsResource;
use App\Models\Seminar;
use App\Models\SpecialPersonal;
use App\Models\SpecialPersonnelForms;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SpecialPersonnelFormsController extends Controller
{
    use HttpResponses;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, Seminar $seminar)
    {
        $forms = SpecialPersonnelForms::where('seminar_id', $seminar->id);
        return SpecialPersonnelFormsResource::collection($forms->paginate($request->perPage));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, SpecialPersonal $personnel)
    {
        if($personnel->specialPersonnelForm->count() > 0) {
            return $this->error(new SpecialPersonalsResource($personnel), 'Već posjeduje ispunjenu formu!', 401);
        }
        $request->validate([
            'nameOfParent' => 'required|string|max:191',
            'jmbg' => 'required|numeric|digits_between:13,13',
            'birthDate' => 'required|date',
            'birthPlace' => 'required|string|max:191',
            'address' => 'required|string|max:191',
            'landlinePhone' => 'string|max:191',
            'belt' => 'required|string|max:191',
            'beltAcquired' => 'required|date',
            'certificate' => 'required|string|max:191',
            'certificateId' => 'required|string|max:191',
            'certificateAcquired' => 'required|date',
            'certificateIssuer' => 'required|string|max:191',
            'policyConfirmation'=> 'required|boolean',
        ]);
        $isJudge = $personnel->role == 1;
        $isCoach = $personnel->role == 2;
        if($isJudge) {
            $request->validate([
                'judgeTitle' => 'required|string|max:191',
                'judgeTitleAcquired' => 'required|date'
            ]);
            $application = SpecialPersonnelForms::create([
                'personnel_id' => $personnel->id,
                'name_of_parent' => $request->nameOfParent,
                'jmbg' => $request->jmbg,
                'birth_date' => $request->birthDate,
                'birth_place' => $request->birthPlace,
                'address' => $request->address,
                'landline_phone' => $request->landlinePhone,
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
        if($isCoach) {
            $request->validate([
                'forCategories' => 'required|string|max:191',
            ]);
            $application = SpecialPersonnelForms::create([
                'personnel_id' => $personnel->id,
                'name_of_parent' => $request->nameOfParent,
                'jmbg' => $request->jmbg,
                'birth_date' => $request->birthDate,
                'birth_place' => $request->birthPlace,
                'address' => $request->address,
                'landline_phone' => $request->landlinePhone,
                'belt' => $request->belt,
                'belt_acquired' => $request->beltAcquired,
                'certificate' => $request->certificate,
                'certificate_id' => $request->certificateId,
                'certificate_acquired' => $request->certificateAcquired,
                'certificate_issuer' => $request->certificateIssuer,
                'policy_confirmation'=> $request->policyConfirmation,
                'for_categories' => $request->forCategories,
            ]);
        }
        //email notification with form data i view blade
        return $this->success(new SpecialPersonnelFormsResource($application), 'Uspješno kreirana forma!');
       
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, SpecialPersonnelForms $specialPersonnelForms)
    {
        if(Auth::user()->user_type != 2 && Auth::user()->status == 0) {
            return $this->error('', 'Ovaj nalog je trenutno suspendovan.', 403);
        }

        $request->validate([
            'nameOfParent' => 'string|max:191',
            'jmbg' => 'numeric|digits_between:13,13',
            'birthDate' => 'date',
            'birthPlace' => 'string|max:191',
            'address' => 'string|max:191',
            'landlinePhone' => 'string|max:191',
            'belt' => 'string|max:191',
            'beltAcquired' => 'date',
            'certificate' => 'string|max:191',
            'certificateId' => 'string|max:191',
            'certificateAcquired' => 'date',
            'certificateIssuer' => 'string|max:191',
            'policyConfirmation'=> 'boolean',
            'judgeTitle' => 'string|max:191',
            'judgeTitleAcquired' => 'date',
            'forCategories' => 'string|max:191',
        ]);
        $request->has('nameOfParent') ? $specialPersonnelForms->update(['name_of_parent' => $request->nameOfParent]) : null;
        $request->has('jmbg') ? $specialPersonnelForms->update(['jmbg' => $request->jmbg]) : null;
        $request->has('birthDate') ? $specialPersonnelForms->update(['birth_date' => $request->birthDate]) : null;
        $request->has('birthPlace') ? $specialPersonnelForms->update(['birth_place' => $request->birthPlace]) : null;
        $request->has('address') ? $specialPersonnelForms->update(['address' => $request->address]) : null;
        $request->has('landlinePhone') ? $specialPersonnelForms->update(['landline_phone' => $request->landlinePhone]) : null;
        $request->has('belt') ? $specialPersonnelForms->update(['belt' => $request->belt]) : null;
        $request->has('beltAcquired') ? $specialPersonnelForms->update(['belt_acquired' => $request->beltAcquired]) : null;
        $request->has('certificate') ? $specialPersonnelForms->update(['certificate' => $request->certificate]) : null;
        $request->has('certificateId') ? $specialPersonnelForms->update(['certificate_id' => $request->certificateId]) : null;
        $request->has('certificateAcquired') ? $specialPersonnelForms->update(['certificate_acquired' => $request->certificateAcquired]) : null;
        $request->has('certificateIssuer') ? $specialPersonnelForms->update(['certificate_issuer' => $request->certificateIssuer]) : null;
        $request->has('policyConfirmation') ? $specialPersonnelForms->update(['policy_confirmation' => $request->policyConfirmation]) : null;
        $request->has('judgeTitle') ? $specialPersonnelForms->update(['judge_title' => $request->judgeTitle]) : null;
        $request->has('judgeTitleAcquired') ? $specialPersonnelForms->update(['judge_title_acquired' => $request->judgeTitleAcquired]) : null;
        $request->has('forCategories') ? $specialPersonnelForms->update(['for_categories' => $request->forCategories]) : null;

        return $this->success('', 'Uspješno dopunjena forma!');
    }
}