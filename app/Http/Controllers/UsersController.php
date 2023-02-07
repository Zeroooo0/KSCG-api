<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UsersResource;
use App\Models\User;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UsersController extends Controller
{
    use HttpResponses;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        
        if(Auth::user()->user_type !== 2){
            return $this->restricted('', 'Not alowed!', 403);
        }
        return UsersResource::collection(
            User::paginate(5)
        );
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreUserRequest $request)
    {
        if(Auth::user()->user_type !== 2){
            return $this->restricted('', 'Not alowed!', 403);
        }
        $user_type = Auth::user()->user_type == 2 ? $request->user_type : 0;
        $request->validated($request->all());

        $user = User::create([
            'name' => $request->name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'user_type' => $user_type,
            'status' => Auth::user()->user_type == 2 ? 1 : 0
        ]);

        return new UsersResource($user);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {
        if(Auth::user()->user_type !== 2){
            return $this->restricted('', 'Not alowed!', 403);
        }
        return new UsersResource($user);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateUserRequest $request, User $user)
    {
        $request->validated($request->all());
        if(Auth::user()->user_type == 0 && $user->id !== Auth::user()->id) {
            return $this->restricted('', 'Not alowed 1!', 403);
        }
        if(Auth::user()->user_type == 0) {
            if($request->user_type !== null || $request->status !== null) {
                return $this->restricted('', 'Not alowed 2!', 403);
            }
        }
        if(Auth::user()->user_type == 1 && $request->user_type > 1) {
            return $this->restricted('', 'Not alowed3!', 403);
        }
        $request->validated($request->all());
        $user->update($request->except(['password']));
        if ($request->has('password')) {
            if(Auth::user()->user_type == 2) {
                $user->update([
                    'password' => Hash::make($request->password)
                ]);
            }
        }
        
        //Must create password resetform
        
        return new UsersResource($user);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        if(Auth::user()->user_type !== 2){
            return $this->restricted('', 'Not alowed!', 403);
        }
        $user->delete();

        return $this->success('', 'User is delten', 200);
    }
}
