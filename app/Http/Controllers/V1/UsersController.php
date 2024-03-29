<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Filters\UsersFilter;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UsersResource;
use App\Models\Club;
use App\Models\SpecialPersonal;
use App\Models\User;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsersController extends Controller
{
    use HttpResponses;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $filter = new UsersFilter();
        $queryItems = $filter->transform($request); //[['column', 'operator', 'value']]
        $per_page = $request->perPage;
        $sort = $request->sort == null ? 'id' : $request->sort;
        $sortDirection = $request->sortDirection == null ? 'desc' : $request->sortDirection;
        $user = User::orderBy($sort, $sortDirection);

        $search = '%'. $request->search . '%';
        if($request->notConnected == true) {
            $clubs = Club::where('user_id', '!=', null)->get();
            $clubs_used = [];
            foreach($clubs as $data) {
                $input[] = $data->user_id;
                $clubs_used = $input;
            }
       
            $user->where($queryItems)->where(DB::raw('CONCAT_WS(" ", name, last_name, email)'), 'like', $search)->whereNotIn('id', $clubs_used)->get();
        }
        if(Auth::user()->user_type != 2) {
            return UsersResource::collection(
                $user->where('id', Auth::user()->id)->get()
            );
        }
        return UsersResource::collection(
            $user->where($queryItems)->where(DB::raw('CONCAT_WS(" ", name, last_name, email)'), 'like', $search)->paginate($per_page)
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
        
        if(Auth::user()->user_type != 2){
            return $this->restricted('', 'Not alowed!', 403);
        }
        $user_type = Auth::user()->user_type == 2 ? $request->userType : 0;
        $request->validated($request->all());

        $user = User::create([
            'name' => $request->name,
            'last_name' => $request->lastName,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'user_type' => $user_type,
            'status' => Auth::user()->user_type == 2 ? 1 : 0
        ]);
        if($user_type == 4) {
            $request->validated([
                'personelId' => 'required|integer'
            ]);
            $personnel = SpecialPersonal::where('id', $request->personnelId)->first();
            $personnel->update([
                'user_id' => $user->id,
            ]);
        }
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
        if(Auth::user()->user_type != 2 && $user->id != Auth::user()->id){
            return $this->restricted('', 'Nedozvoljena akcija!', 403);
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
        if(Auth::user()->user_type != 2 && $user->id != Auth::user()->id) {
            return $this->restricted('', 'Not alowed!', 403);
        }
        if(Auth::user()->user_type != 2) {
            if($request->status != 0) {
                return $this->restricted('', 'Not alowed!', 403);
            }
        }
        $user->update($request->except(['password', 'status', 'userType', 'email']));
        if($request->has('status')) {
            if(Auth::user()->user_type == 2 || (Auth::user()->user_type == 1 && Auth::user()->status == 1)) {
                $user->update(['status' => $request->status]);
                if($user->club != null) {
                    $user->club->update(['status' => $request->status]);
                }
            }
        }
        if($request->has('password') && $request->password != null) {
            $user->update([
                'password' => Hash::make($request->password),
                'passwordConfirmation' => Hash::make($request->passwordConfirmation)
            ]);
            return $this->success('', 'Uspješno izmjenjen korisnik!');
        }
        if($request->has('email')) {
            $emailCheck = User::where('email', $request->email)->get()->count();
            if($user->email == $request->email && $emailCheck > 1) {
                return $this->error('', 'Ovaj mail je zauzet!', 401);
            }
            $user->update([
                'email' => $request->email
            ]);
        }
        if($request->has('userType')) {
            if($user->user_type == 2) {
                return $this->error('','Nije moguće degradirati administratora!',403);
            }
            if(Auth::user()->user_type == 2 || (Auth::user()->user_type == 1 && Auth::user()->status == 1)) {
                $user->update(['user_type' => $request->userType]);
            }
        }
        
        
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
        if(Auth::user()->user_type != 2){
            return $this->restricted('', 'Nije vam dozvoljeno brisanje!', 403);
        }
        if($user->user_type == 2) {
            return $this->restricted('', 'Nije vam dozvoljeno da obrišete Administratora!', 403);
        }
        if($user->user_type == 0 && $user->club != null) {
            $user->club->update([
                'user_id'=> null,
                'status'=> 0
            ]);
        }
        $user->delete();

        return $this->success('', 'Korisnik je obrisan', 200);
    }
}
