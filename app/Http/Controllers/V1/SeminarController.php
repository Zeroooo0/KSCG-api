<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSeminarRequest;
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

        if(Auth::user() == null) {
            return SeminarResource::collection(Seminar::orderBy('id', 'desc')->where('is_hidden', 0)->paginate($request->perPage));
        } else {
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
        if(Auth::user()->user_type == 0) {
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
    public function update(Seminar $seminar, Request $request)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Seminar $seminar)
    {
        if(Auth::user()->user_type != 2){
            return $this->error('', 'Brisanje je dozvoljeno samo adminu!', 404);
        }
        $seminar->delete();
        return $this->success('', 'Uspješno obrisan seminar');
    }
}
