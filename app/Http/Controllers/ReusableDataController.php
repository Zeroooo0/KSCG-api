<?php

namespace App\Http\Controllers;

use App\Http\Requests\BulkBeltsStoreRequest;
use App\Http\Requests\StoreClubAdministration;
use App\Http\Resources\ClubsResource;
use App\Models\Belt;
use App\Models\Club;
use App\Models\Roles;
use App\Models\SpecialPersonal;
use App\Traits\HttpResponses;
use Illuminate\Support\Facades\Auth;

class ReusableDataController extends Controller
{
    use HttpResponses;
    public function bulkStoreBelts(BulkBeltsStoreRequest $request)
    {

        if(Auth::user()->user_type != 2) {
            return $this->error('', 'Dodavanje pojaseva je dozvoljeno samo super administratoru!', 406);
        }

        $data = [];
        
        foreach($request->all() as $val) {
            $input['name'] = $val['name'];
            $input['hash_color'] = $val['hashColor'];
            $data[] = $input;
        }
        
        Belt::insert($data);
        return $this->success('', $data);
    }

    public function clubsAdministration(StoreClubAdministration $request)
    {
        $request->validated($request->all());

        $club = Club::where('id', $request->clubId)->first();
        $spec_personal = SpecialPersonal::where('id', $request->specialPersonalId)->first();

        if($spec_personal->role == 1) {
            return $this->error('', 'Sudije ne mogu biti registrovane kao uprava Kluba!', 403);
        }
        if($spec_personal->role == 2 && Auth::user()->user_type == 0) {
            return $this->error('', 'Trener moze dodati samo administrator!', 403);
        }
        if(Auth::user()->user_type != 2 && Auth::user()->status == 0) {
            return $this->error('', 'Nalog je suspendovan!', 403);
        }

        $rolesExistance = Roles::where([['special_personals_id',  $request->specialPersonalId], ['roleable_type', 'App\Models\Club']])->count() >= 1 ? true : false;

        if($rolesExistance) {
            $roleIdOfClub = Roles::where([['special_personals_id',  $request->specialPersonalId], ['roleable_type', 'App\Models\Club']])->get()->first()->roleable_id;
            $roleInClub = Club::find($roleIdOfClub)->name;
            if($club->roles()->where('special_personals_id', $request->specialPersonalId)->count() >= 1) {
                return $this->error('', 'Već je prijavljen u vašem klubu!', 406);
            }
            if((string)$roleIdOfClub != (string)$request->clubId) {
                return $this->error('', 'Trener je već angažovan u KK ' . $roleInClub, 406);
            }
        } else {
            $club->roles()->create([
                'special_personals_id' => $request->specialPersonalId,
                'title' => $request->title,
                'role' => $spec_personal->role
            ]);
            return new ClubsResource($club);
        }
    }

    public function deleteRole(Roles $roles)
    {
        $specialPersonal = SpecialPersonal::where('id', $roles->special_personal_id)->first();
        //return response(Auth::user()->user_type == 0);
        if(Auth::user()->user_type == 0 && Auth::user()->state == 0 && ($roles->role != 0 || $roles->role != 3)) {
            return $this->error('', 'Mozete sami da uklonit upravu kluba za ostalo je potrebnod da kontaktirate Administratora!', 406);
        }
        if(Auth::user()->user_type != 2 && Auth::user()->state == 0) {
            return $this->error('', 'Vas nalog je suspendovan!', 406);
        }
        $roles->delete();
        return $this->success('', 'Uloga je obrisana!');
    }

}
