<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSeminarRequest;
use App\Http\Requests\UpdateSeminarRequest;
use App\Http\Resources\SeminarResource;
use App\Models\Seminar;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SeminarController extends Controller
{
    use HttpResponses;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $notAlowed = [0, 3, 4];

        if(Auth::user() == null ) {
            return SeminarResource::collection(Seminar::orderBy('id', 'desc')->where('is_hidden', 0)->paginate($request->perPage));
        } 
        if(Auth::user() != null && in_array(Auth::user()->user_type, $notAlowed) ) {

        }
        if(Auth::user()->user_type != 2) {
            return SeminarResource::collection(Seminar::orderBy('id', 'desc')->paginate($request->perPage));
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreSeminarRequest $request)
    {
        $notAlowed = [0, 3, 4];
        if(in_array(Auth::user()->user_type, $notAlowed) ) {
            return $this->error('', 'Ova fnkcionalnost vam nije dozvoljena!', 404);
        }
        $request->validated($request->all());
        $seminar = Seminar::create([
            'name' => $request->name,
            'deadline' => $request->deadline,
            'start' => $request->start,
            'address' => $request->address,
            'seminar_type' => $request->seminarType,
            'has_judge' => $request->hasJudge,
            'has_compatitor' => $request->hasCompetitor,
            'has_coach' => $request->hasCoach,
            'price_judge' => $request->priceJudge,
            'price_compatitor' => $request->priceCompatitor,
            'price_coach' => $request->priceCoach,
            'is_hidden' => $request->isHidden,
            'is_paid' => $request->isPaid,

        ]);
        return $this->success(new SeminarResource($seminar), 'Uspješno krerian seminar');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Seminar $seminar)
    {
        return new SeminarResource($seminar);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Seminar $seminar, UpdateSeminarRequest $request)
    {
        $notAlowed = [0, 3, 4];
        if(in_array(Auth::user()->user_type, $notAlowed) ) {
            return $this->error('', 'Ova fnkcionalnost vam nije dozvoljena!', 404);
        }
        $request->validated($request->all());
        $seminar->update(
            $request->except(['seminarType', 'hasJudge', 'hasCompatitor', 'hasCoach', 'priceJudge', 'priceCompatitor', 'priceCoach', 'isHidden', 'isPaid'])
        );
        $request->has('seminarType') ? $seminar->update(['seminar_type' => $request->seminarType]) : null;
        $request->has('hasJudge') ? $seminar->update(['has_judge' => $request->hasJudge]) : null;
        $request->has('hasCompatitor') ? $seminar->update(['has_compatitor' => $request->hasCompatitor]) : null;
        $request->has('hasCoach') ? $seminar->update(['has_coach' => $request->hasCoach]) : null;
        $request->has('priceJudge') ? $seminar->update(['price_judge' => $request->priceJudge]) : null;
        $request->has('priceCompatitor') ? $seminar->update(['price_compatitor' => $request->priceCompatitor]) : null;
        $request->has('priceCoach') ? $seminar->update(['price_coach' => $request->priceCoach]) : null;
        $request->has('isHidden') ? $seminar->update(['is_hidden' => $request->isHidden]) : null;
        $request->has('isPaid') ? $seminar->update(['is_paid' => $request->isPaid]) : null;

        return $this->success(new SeminarResource($seminar), 'Uspješno izmjenjen seminar');

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Seminar $seminar)
    {
        $notAlowed = [0, 3, 4];
        if(in_array(Auth::user()->user_type, $notAlowed) ) {
            return $this->error('', 'Ova fnkcionalnost vam nije dozvoljena!', 404);
        }
        $seminar->delete();
        return $this->success('', 'Uspješno obrisan seminar');
    }
}
