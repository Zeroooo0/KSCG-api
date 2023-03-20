<?php

namespace App\Http\Controllers;

use App\Filters\SpecialPersonalsFilter;
use App\Http\Requests\StoreSpecialPersonalRequest;
use App\Http\Requests\UpdateSpecialPersonalRequest;
use App\Http\Resources\SpecialPersonalsResource;
use App\Models\Club;
use App\Models\SpecialPersonal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SpecialPersonalsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $filter = new SpecialPersonalsFilter();
        $queryItems = $filter->transform($request); //[['column', 'operator', 'value']]
        $per_page = $request->perPage;
        $sort = $request->sort == null ? 'id' : $request->sort;
        $sortDirection = $request->sortDirection == null ? 'desc' : $request->sortDirection;
        $specialPersonal = SpecialPersonal::orderBy($sort, $sortDirection);
        
        $search = '%'. $request->search . '%';
        if(Auth::user()->user_type == 0){
            $club_personal = Club::where('id', Auth::user()->club->id)->first()->roles;
            $spec_personal = [];
            foreach($club_personal as $id) {
                $spec_personal[] = $id->special_personals_id;
              
            }
            //return response($spec_personal);
            return SpecialPersonalsResource::collection($specialPersonal->whereIn('id', $spec_personal)->where($queryItems)->where(DB::raw('CONCAT_WS(" ", name, last_name, email)'), 'like', $search)->paginate($per_page));
    
        }
        return SpecialPersonalsResource::collection($specialPersonal->where($queryItems)->where(DB::raw('CONCAT_WS(" ", name, last_name, email)'), 'like', $search)->paginate($per_page));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreSpecialPersonalRequest $request)
    {
        $request->validated($request->all());
        if(Auth::user()->user_type == 0) {
            $role = 0; 
        } else{
            if($request->role == null){
                $role = 0; 
            }
            $role = $request->role;
        }

        $special_personal = SpecialPersonal::create([
            'name' => $request->name,
            'last_name' => $request->lastName,
            'email' => $request->email,
            'country' => $request->country,
            'phone_number' => $request->phone,
            'role' => $role,
            'gender' => $request->gender,
        ]);
        if($request->image != null){
            $path = Storage::putFile('special-personal-image', $request->image);
            $special_personal->image()->create([
                'url' => $path
            ]);
        }
        return new SpecialPersonalsResource($special_personal);

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(SpecialPersonal $special_personal)
    {
        return new SpecialPersonalsResource($special_personal);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateSpecialPersonalRequest $request, SpecialPersonal $special_personal)
    {
        $request->validated($request->all());
        if($request->role != null && Auth::user()->user_type == 0) {
            $role = $special_personal->role; 
        } else{
            $role = $request->role;

            
        }
        $special_personal->update($request->except('lastName', 'role', 'status'));
        if($request->has('lastName')){ 
            $special_personal->update([
                'last_name' => $request->lastName
            ]);
        }
        if($request->has('role')){ 
            $special_personal->update([
                'role' => $role
            ]);
        }
        if($request->has('status')){ 
            if(Auth::user()->user_type != 0){
                $special_personal->update([
                    'status' => $request->status
                ]);
            }
        }

        return new SpecialPersonalsResource($special_personal);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(SpecialPersonal $personal)
    {
        if(Auth::user()->user_type != 2){
            return $this->restricted('', 'Not alowed!', 403);
        }
        foreach($personal->image()->get() as $image) {
            Storage::delete($image->url);
        }
        foreach($personal->document()->get() as $document) {
            Storage::delete($document->url);
        }
        $personal->image()->delete();
        $personal->document()->delete();
        $personal->delete();
        return $this->success('', 'Uspješno Obrisano!', 200);
    }
}
