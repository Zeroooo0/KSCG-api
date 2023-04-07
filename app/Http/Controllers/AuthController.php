<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdminUserCreation;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\LoginUserRequest;
use App\Http\Requests\StoreUserRequest;
use App\Http\Resources\UsersResource;
use App\Models\Club;
use App\Models\User;
use App\Notifications\ForgotPassword;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;
use Illuminate\Validation\Rules;

class AuthController extends Controller
{
    use HttpResponses;

    public function login(LoginUserRequest $request)
    {
        $request->validated($request->all());

        if(!Auth::attempt($request->only('email', 'password'))) {
            return $this->error('', 'Pogrešno unešeni email ili password!', 401);
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
        ], 'Uspješno ste ulogovni!');
    }

    public function register(StoreUserRequest $request)
    {
        $request->validated($request->all());

        $user = User::create([
            'name' => $request->name,
            'last_name' => $request->lastName,
            'email' => $request->email,
            'password' => Hash::make($request->password)
        ]);
        if($user->user_type == 0) {
            $token_ability = ['club'];
        } 

        if($user->user_type == 1) {
            $token_ability = ['commission'];
        }
   
            
        if($user->user_type == 2) {
            $token_ability = ['admin'];
        }
        


        return $this->success([
            'user' => new UsersResource($user),
            'authToken' => $user->createToken('API token of ' . $user->name . ' '. $user->last_name, $token_ability)->plainTextToken
        ], 'Uspješno ste registrovani!');

    }

    public function logout()
    {

        Auth::user()->currentAccessToken()->delete();

        return $this->success('', 'Uspješno ste izlogovani!');
    }
    public function changePassword(ChangePasswordRequest $request, User $user)
    {
        $request->validated($request->all());
        if(Auth::user()->id == $user->id) {
            if(!Hash::check($request->old_password, Auth::user()->password)){
                return $this->error('', 'Šifra se ne poklapa', 200);
            }
            $user->update([
                'password' => Hash::make($request->password)
            ]);
            return $this->success([
                'user' => new UsersResource($user),
            ], 'Uspješno ste izmjenili šifru!');
        }

    }
    public function forgotPasswordNotification(Request $request)
    {     
        $request->validate(['email'=> ['required','email', 'exists:users,email']]);
        $user = User::where('email', $request->email)->first();
        $user->notify(new ForgotPassword());
        return $this->success('','Uspješno ste poslali email: '. $request->email);

    }
    public function resetForgotenPassword(Request $request)
    {

        $request->validate(['password'=> ['required','confirmed', Rules\Password::defaults()]]);
        Auth::user()->currentAccessToken()->delete();
        return $this->success('','Uspjesno izmjenjena sifra!');
    }
    public function checkToken() {
        $token = Auth::user()->currentAccessToken();
        if(now() >= $token->created_at->addMinutes(5)){
            $token->delete();
            return $this->error('', 'Token je istekao!', 403);
        }
        return $this->success('','Token je još validan!');
    }
}
    
