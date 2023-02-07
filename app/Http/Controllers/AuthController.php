<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdminUserCreation;
use App\Http\Requests\LoginUserRequest;
use App\Http\Requests\StoreUserRequest;
use App\Http\Resources\UsersResource;
use App\Models\Club;
use App\Models\User;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    use HttpResponses;

    public function login(LoginUserRequest $request)
    {
        $request->validated($request->all());

        if(!Auth::attempt($request->only('email', 'password'))) {
            return $this->error('', 'Credentials do not match', 401);
        }  

        $user = User::where('email', $request->email)->first();

        if(Auth::user()->status == 1) {
            if(Auth::user()->user_type == 0) {
                $token_ability = ['club'];
            } 
    
            if(Auth::user()->user_type == 1) {
                $token_ability = ['commission'];
            }
        } else {
            $token_ability = [];
        }
            
        if(Auth::user()->user_type == 2) {
            $token_ability = ['admin'];
        }
        
        return $this->success([
            'user' => new UsersResource($user),
            'token' => $user->createToken('API token of ' . $user->name . ' '. $user->last_name, $token_ability)->plainTextToken
        ]);
    }

    public function register(StoreUserRequest $request)
    {
        $request->validated($request->all());
        
        $user = User::create([
            'name' => $request->name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => Hash::make($request->password)
        ]);
        
        return $this->success([
            'user' => $user,
        ]);
    }

    public function logout()
    {
        Auth::user()->currentAccessToken()->delete();

        return $this->success([
            'message' => 'You have logged out Best Regards!'
        ]);
    }
}
    
