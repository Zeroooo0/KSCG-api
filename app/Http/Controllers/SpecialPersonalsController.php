<?php

namespace App\Http\Controllers;

use App\Http\Resources\SpecialPersonalsResource;
use App\Models\SpecialPersonal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
    public function store(Request $request)
    {
        if(Auth::user()->user_type == 0) {

        }
        $special_personal = SpecialPersonal::create([
            'name' => $request->name,
            'last_name' => $request->lastName,
            'email' => $request->email,
            'phone_number' => $request->phone,
            'rolle' => $request->rolle,
            'status' => $request->status,
            'gender' => $request->gender,
            'image' => $path

        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
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
