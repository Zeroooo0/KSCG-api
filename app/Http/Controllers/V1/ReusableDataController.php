<?php

namespace App\Http\Controllers\V1;

use App\Filters\ClubsFilter;
use App\Http\Controllers\Controller;
use App\Filters\CompatitorsFilter;
use App\Filters\RoleFilter;
use App\Http\Requests\BulkBeltsStoreRequest;
use App\Http\Requests\StoreClubAdministration;
use App\Http\Resources\BeltResource;
use App\Http\Resources\ClubsOnCompatitionResource;
use App\Http\Resources\ClubsResource;
use App\Http\Resources\CompatitorsResource;
use App\Http\Resources\ResultsResource;
use App\Http\Resources\RolesResource;
use App\Http\Resources\SpecialPersonalsResource;
use App\Models\Belt;
use App\Models\Club;
use App\Models\Compatition;
use App\Models\Compatitor;
use App\Models\Component;
use App\Models\Image;
use App\Models\Registration;
use App\Models\Roles;
use App\Models\SpecialPersonal;
use App\Support\Collection;
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
        /*
        if($spec_personal->role == 2 && Auth::user()->user_type == 0) {
            return $this->error('', 'Trener moze dodati samo administrator!', 403);
        }
        if(Auth::user()->user_type != 2 && Auth::user()->status == 0) {
            return $this->error('', 'Nalog je suspendovan!', 403);
        }
        */
        $rolesExistance = Roles::where([['special_personals_id',  $request->specialPersonalId], ['roleable_type', 'App\Models\Club']])->count() >= 1 ? true : false;

        if($rolesExistance) {
            $roleIdOfClub = Roles::where([['special_personals_id',  $request->specialPersonalId], ['roleable_type', 'App\Models\Club']])->get()->first()->roleable_id;
            $roleInClub = Club::find($roleIdOfClub)->name;
            if($club->roles()->where('special_personals_id', $request->specialPersonalId)->count() > 1) {
                return $this->error('', 'Već je prijavljen u vašem klubu!', 406);
            }
            if((string)$roleIdOfClub != (string)$request->clubId) {
                return $this->error('', 'Trener je već angažovan u KK ' . $roleInClub, 406);
            }
        } else {
            $role = $spec_personal->role == 2 ? 'Trener' : $request->title;
            $club->roles()->create([
                'special_personals_id' => $request->specialPersonalId,
                'title' => $role,
                'role' => $spec_personal->role
            ]);
            return new ClubsResource($club);
        }
    }

    public function getClubsAdministration(Request $request, Club $club)
    {
        $filter = new RoleFilter();
        $queryItems = $filter->transform($request); //[['column', 'operator', 'value']]
        $clubRolles = $club->roles()->get();
        $clubsRollesIds = [];
        foreach($clubRolles as $data){
            $specialPersonal = SpecialPersonal::where('id', $data->special_personals_id)->first();
            if($request->removeInactive == 1){
                if($specialPersonal->status == 1) {
                    $clubsRollesIds[] = $data->special_personals_id;
                }
            } else {
                $clubsRollesIds[] = $data->special_personals_id;
            }

        }
        //return $clubsRollesIds;
        return RolesResource::collection(Roles::whereIn('special_personals_id', $clubsRollesIds)->where('roleable_type', 'App\Models\Club')->where('roleable_id', $club->id)->where($queryItems)->paginate($request->perPage));
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
        $roles->delete();
        return $this->success('', 'Uloga je obrisana!');
    }
    public function clubsResults(Request $request, Club $club)
    {
        $perPage = $request->per_page;
        return ResultsResource::collection(Registration::where('club_id', $club->id)->paginate($perPage));
    }
    public function registeredClubs(Request $request) 
    {
        $filter = new ClubsFilter();
        $queryItems = $filter->transform($request); //[['column', 'operator', 'value']]
        $sort = $request->sort == null ? 'id' : $request->sort;
        $sortDirection = $request->sortDirection == null ? 'desc' : $request->sortDirection;
        if(!$request->has('competitionId'))
        {
            return $this->error('', 'Mora da postoji parametar competitionId={id}', 422);
        }

        $per_page = $request->has('perPage') ? $request->perPage : 15;
        $competition = Compatition::where('id', $request->competitionId)->first();
        $registrations = $competition->registrations;
        $clubs = [];

        foreach ($registrations->countBy('club_id') as $club=>$val) {   
            $singleRegistrations = $registrations->where('status', 1)->where('team_or_single', 1)->where('club_id', $club);
            $teamRegistrations = $registrations->where('status', 1)->where('team_or_single', 0)->where('club_id', $club);
            $gold = $singleRegistrations->where('position', 3)->count() + $teamRegistrations->where('position', 3)->countBy('team_id')->count();
            $silver = $singleRegistrations->where('position', 2)->count() + $teamRegistrations->where('position', 2)->countBy('team_id')->count();
            $bronze = $singleRegistrations->where('position', 1)->count() + $teamRegistrations->where('position', 1)->countBy('team_id')->count();
            $input['club_id'] = $club;            
            $input['gold'] = $gold;
            $input['silver'] = $silver;
            $input['bronze'] = $bronze;
            $clubs[] = $input;
            
        }


        $sortingClubs = collect($clubs)->sortByDesc('bronze')->sortByDesc('silver')->sortByDesc('gold');
        $sortedClubs = [];
        foreach($sortingClubs as $club) {
            $sortedClubs[] = Club::where('id', $club['club_id'])->first();
        }
        
        if($request->has('id')){
            return ClubsOnCompatitionResource::collection(Club::where('id', $request->id['eq'])->where($queryItems)->paginate());
        }
        return ClubsOnCompatitionResource::collection((new Collection($sortedClubs))->paginate($per_page));
    }
    public function competitionRoles(Compatition $competition, Request $request)
    {
        $per_page = !$request->has('perPage') ? 15 : $request->perPage;
        $specPersonnels = [];
        foreach($competition->roles as $val) {
            $specPersonnels[] = $val->special_personals_id;
        }
       
        return RolesResource::collection((new Collection($competition->roles))->paginate($per_page));
    }

    public function storeComponentRole(Request $request, Component $component)
    {
        $specialPersonal = SpecialPersonal::where('id', $request->specPersonId)->first();
  
        $role = $component->roles()->create([
            'special_personals_id' => $request->specPersonId,
            'title' => $request->title,
            'role' => $specialPersonal->role
        ]);
        return $this->success(new RolesResource($role), 'Uspjesno dodata uloga');
    }

    public function specPersonnelCompetitionRoles(SpecialPersonal $specPersonnels, Request $request)
    {
        $per_page = $request->perPage;
        $roles = Roles::where('special_personals_id', $specPersonnels->id)->where('roleable_type', 'App\Models\Compatition');

        return RolesResource::collection($roles->paginate($per_page));
    }
    public function specPersonnelRoles(SpecialPersonal $specPersonnels, Request $request)
    {
        $per_page = $request->perPage;
        $roles = Roles::where('special_personals_id', $specPersonnels->id);

        return RolesResource::collection($roles->orderBy('id', 'desc')->paginate($per_page));
    }

    public function deleteImage(Image $image) 
    {
        $image->delete();
        return $this->success('', 'Slika uspješno obrisana');
    }
}
