<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSpecialPersonalRequest;
use App\Http\Requests\UpdateSpecialPersonalRequest;
use App\Http\Resources\SpecialPersonalsResource;
use App\Models\SpecialPersonal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class SpecialPersonalsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return SpecialPersonalsResource::collection(SpecialPersonal::paginate(15));
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
            $rolle = 0; 
        } else{
            if($request->rolle == null){
                $rolle = 0; 
            }
            $rolle = $request->rolle;
        }

        $special_personal = SpecialPersonal::create([
            'name' => $request->name,
            'last_name' => $request->lastName,
            'email' => $request->email,
            'country' => $request->country,
            'phone_number' => $request->phone,
            'rolle' => $rolle,
            'gender' => $request->gender,
        ]);
        if($request->image !== null){
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
        if($request->rolle !== null && Auth::user()->user_type == 0) {
            $rolle = $special_personal->rolle; 
        } else{
            $rolle = $request->rolle;

            
        }
        $special_personal->update($request->except('lastName', 'rolle', 'status'));
        if($request->has('lastName')){ 
            $special_personal->update([
                'last_name' => $request->lastName
            ]);
        }
        if($request->has('rolle')){ 
            $special_personal->update([
                'rolle' => $rolle
            ]);
        }
        if($request->has('status')){ 
            if(Auth::user()->user_type !== 0){
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
        if(Auth::user()->user_type !== 2){
            return $this->restricted('', 'Not alowed!', 403);
        }
        $personal->delete();
        return $this->success('', 'Compatitor is delten!', 200);
    }
}
