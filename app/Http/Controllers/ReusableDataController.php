<?php

namespace App\Http\Controllers;

use App\Filters\CompatitorsFilter;
use App\Http\Requests\BulkBeltsStoreRequest;
use App\Http\Requests\StoreClubAdministration;
use App\Http\Resources\BeltResource;
use App\Http\Resources\ClubsResource;
use App\Http\Resources\CompatitorsResource;
use App\Http\Resources\ResultsResource;
use App\Http\Resources\RolesResource;
use App\Http\Resources\SpecialPersonalsResource;
use App\Models\Belt;
use App\Models\Club;
use App\Models\Compatitor;
use App\Models\Registration;
use App\Models\Roles;
use App\Models\SpecialPersonal;
use App\Traits\HttpResponses;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReusableDataController extends Controller
{
    use HttpResponses;

    public function index()
    {
        return BeltResource::collection(Belt::all());
    }
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
        return $this->success($data, 'Uspješno dodati pojasevi.');
    }
    public function bulkStore(Request $request)
    {

        if(Auth::user()->user_type != 2) {
            return $this->error('', 'Dodavanje pojaseva je dozvoljeno samo super administratoru!', 406);
        }
        $belt = Belt::create([
            'name' => $request->name,
            'hash_color' => $request->hashColor
        ]);

        
        return new BeltResource($belt);
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

    public function getClubsAdministration(Request $request, Club $club)
    {
        $clubRolles = $club->roles()->get();
        $clubsRollesIds = [];
        foreach($clubRolles as $data){
            $clubsRollesIds[] = $data->special_personals_id;
        }
        //return $clubsRollesIds;
        return RolesResource::collection(Roles::whereIn('id', $clubsRollesIds)->paginate($request->perPage));
    }


    public function clubCompatitors(Request $request, Club $club)
    {
        $filter = new CompatitorsFilter();
        $queryItems = $filter->transform($request); //[['column', 'operator', 'value']]
        $per_page = $request->perPage;
        $sort = $request->sort == null ? 'id' : $request->sort;
        $sortDirection = $request->sortDirection == null ? 'asc' : $request->sortDirection;
        $compatitor = Compatitor::where('club_id',$club->id)->orderBy($sort, $sortDirection);
        $search = '%'. $request->search . '%';

        return CompatitorsResource::collection($compatitor->where($queryItems)->where(DB::raw('CONCAT_WS(" ", name, last_name)'), 'like', $search)->paginate($per_page));
    }

    public function getCompatitorResults(Request $request, Compatitor $competitor)
    {
        $perPage = $request->per_page;

        return ResultsResource::collection(Registration::where('compatitor_id', $competitor->id)->paginate($perPage));
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
    public function clubsResults(Club $club)
    {
        $perPage = $request->per_page;
        return ResultsResource::collection(Registration::where('club_id', $club->id)->paginate($perPage));
    }

}
