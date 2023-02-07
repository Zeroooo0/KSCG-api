<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCompatitorRequest;
use App\Http\Resources\CompatitorsResource;
use App\Models\Club;
use App\Models\Compatitor;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CompatitorsController extends Controller
{
    use HttpResponses;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    
    public function public()
    {
        $per_page = 5;
        return CompatitorsResource::collection(
            Compatitor::paginate($per_page)
        );
    }

    public function protected()
    {
        $per_page = 5;
    
        if(Auth::user()->user_type == 0) {
            return CompatitorsResource::collection(
                Compatitor::where('club_id',  Auth::user()->club->id )->paginate($per_page)
            );
        } else {
            return CompatitorsResource::collection(
                Compatitor::paginate($per_page)
            );
        }
  

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreCompatitorRequest $request)
    {
        if(Auth::user()->user_type !== 2 && Auth::user()->status == 0) {
            return $this->restricted('', 'Not alowed!', 403);
        }
        $request->validated($request->all());
        
        $compatitor = Compatitor::create([
            'club_id' => Auth::user()->user_type == 0 ? Auth::user()->club->id : $request->clubId,
            'kscg_compatitor_id' => $request->kscgId,
            'name' => $request->name,
            'last_name' => $request->lastName,
            'gender' => $request->gender,
            'jmbg' => $request->jmbg,
            'belt' => $request->belt,
            'date_of_birth' => $request->dateOfBirth,
            'weight' => $request->weight,
            'status' => Auth::user()->user_type == 0 ? 0 : 1
        ]);
        if($request->image !== null) {
            $path = Storage::putFile('compatitor-image', $request->image);
            $compatitor->image()->create([
                'url' => $path
            ]);
        }


        return new CompatitorsResource($compatitor);
       
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show_public(Compatitor $compatitor)
    {
        return new CompatitorsResource($compatitor);
    }

    public function show_protected(Compatitor $compatitor)
    {

        if(Auth::user()->user_type == 0 && Auth::user()->club->id !== $compatitor->club_id) {
            return $this->restricted('', 'You are not authorized to make the request!', 403);
        } else {
            return new CompatitorsResource($compatitor);
        }

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Compatitor $compatitor)
    {
        if(Auth::user()->user_type !== 2 && Auth::user()->status == 0) {
            return $this->restricted('', 'Not alowed!', 403);
        }
        if(Auth::user()->user_type == 0 && $compatitor->club_id !== Auth::user()->club->id) {
            return $this->restricted('', 'Not alowed to change this compatitor!', 403);
        }
        $compatitor->update($request->except(['lastName', 'dateOfBirth', 'clubId', 'status']));


        if ($request->has('lastName')) {
            $compatitor->update([
                'last_name' => $request->lastName
            ]);
        }
        if ($request->has('dateOfBirth')) {
            $compatitor->update([
                'date_of_birth' => $request->dateOfBirth
            ]);
        }
        if ($request->has('clubId')) {

            if(Auth::user()->user_type !== 2 || Auth::user()->user_type == 0) {
                return $this->error('', 'Not promited to change compatitors club id!', 403);
            } 

            $compatitor->update([
                'club_id' => $request->clubId
            ]);
        }
        if ($request->has('status')) {

            if(Auth::user()->user_type !== 2 || Auth::user()->user_type == 0) {
                return $this->error('', 'Not promited to change compatitors status!', 403);
            } 

            $compatitor->update([
                'status' => $request->status
            ]);
        }
        if(Auth::user()->user_type == 0 && $request->hasAny(['dateOfBirth', 'weight'])) {
            $compatitor->update([
                'status' => 0
            ]);
        }

        return new CompatitorsResource($compatitor);
 
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Compatitor $compatitor)
    {
        if(Auth::user()->user_type !== 2){
            return $this->restricted('', 'Not alowed!', 403);
        }
        $compatitor->delete();
        return $this->success('', 'Compatitor is delten', 200);
    }



}
