<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCompatitionRequest;
use App\Http\Requests\UpdateCompatitionRequest;
use App\Http\Resources\CompatitionsResource;
use App\Models\Compatition;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CompatitionsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return CompatitionsResource::collection(Compatition::paginate($request->perPage));
    }
    
    public function public(Request $request)
    {
        return CompatitionsResource::collection(Compatition::where('status', 1)->paginate($request->perPage));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreCompatitionRequest $request)
    {
        if(Auth::user()->user_type !== 2 && Auth::user()->status == 0) {
            return $this->restricted('', 'Not alowed!', 403);
        }
        $request->validated($request->all());

        if(Auth::user()->user_type !== 0 && $request->status !== null) {
            $status = $request->status;
        } else {
            $status = false;
        }
        $registrationStatus = $request->has('registrationStatus') ? $request->registrationStatus : true;
        $compatition = Compatition::create([
            'name' => $request->name,
            'host_name' => $request->hostName,
            'start_time_date' => $request->startTimeDate,
            'registration_deadline' => $request->registrationDeadline,
            'price_single' => $request->priceSingle,
            'price_team' => $request->priceTeam,
            'country' => $request->country,
            'city' => $request->city,
            'address' => $request->address,
            'status'=> $status,
            'registration_status' => $registrationStatus
        ]);

        return new CompatitionsResource($compatition);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Compatition $compatition)
    {
        return new CompatitionsResource($compatition);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateCompatitionRequest $request, Compatition $compatition)
    {
        $request->validated($request->all());
        $compatition->update($request->except(['hostName', 'startTimeDate', 'registrationDeadline', 'priceSingle', 'priceTeam', 'registrationStatus', 'status']));
        $request->has('hostName') ? $compatition->update(['host_name' => $request->hostName]) : null;
        $request->has('startTimeDate') ? $compatition->update(['start_time_date' => $request->startTimeDate]) : null;
        $request->has('registrationDeadline') ? $compatition->update(['registration_deadline' => $request->registrationDeadline]) : null;
        $request->has('priceSingle') ? $compatition->update(['price_single' => $request->priceSingle]) : null;
        $request->has('priceTeam') ? $compatition->update(['price_team' => $request->priceTeam]) : null;
        $request->has('registrationStatus') ? $compatition->update(['registration_status' => $request->registrationStatus]) : null;
        $request->has('status') && Auth::user()->user_type !== 0 ? $compatition->update(['status' => $request->status]) : null;

        return new CompatitionsResource($compatition);
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
