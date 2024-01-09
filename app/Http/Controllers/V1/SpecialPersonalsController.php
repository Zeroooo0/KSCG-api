<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Filters\SpecialPersonalsFilter;
use App\Http\Requests\StoreSpecialPersonalRequest;
use App\Http\Requests\UpdateSpecialPersonalRequest;
use App\Http\Resources\SpecialPersonalsResource;
use App\Models\Club;
use App\Models\Compatition;
use App\Models\Component;
use App\Models\Roles;
use App\Models\SpecialPersonal;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use ImagesResize;

class SpecialPersonalsController extends Controller
{
    use HttpResponses;
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

        $search = '%' . $request->search . '%';

        if ($request->has('availableRoles') && $request->availableRoles == true) {
            $club_personal_taken = Roles::where('roleable_type', 'App\Models\Club')->get();
            $spec_personal = [];

            foreach ($club_personal_taken as $id) {
                $spec_personal[] = $id->special_personals_id;
            }

            return SpecialPersonalsResource::collection($specialPersonal->whereNotIn('id', $spec_personal)->where($queryItems)->where(DB::raw('CONCAT_WS(" ", name, last_name, email, country)'), 'like', $search)->get());
        }
        if (Auth::user()->user_type == 0 && $request->has('displayAll')) {
            return SpecialPersonalsResource::collection($specialPersonal->where($queryItems)->where(DB::raw('CONCAT_WS(" ", name, last_name, email, country)'), 'like', $search)->all());
        }
        if (Auth::user()->user_type == 0) {
            $club_personal = Club::where('id', Auth::user()->club->id)->first()->roles;
            $spec_personal = [];
            foreach ($club_personal as $id) {
                $spec_personal[] = $id->special_personals_id;
            }

            $specPerson = $specialPersonal->whereIn('id', $spec_personal)->where($queryItems)->where(DB::raw('CONCAT_WS(" ", name, last_name, email, country)'), 'like', $search);
            return SpecialPersonalsResource::collection($per_page == 0 ? $specPerson->get() : $specPerson->paginate($per_page));
        }
        if (Auth::user()->user_type == 4) {
            $specPerson = $specialPersonal->where('user_id', Auth::user()->id);
            return SpecialPersonalsResource::collection($specPerson->paginate($per_page));
        }
        $specPerson = $specialPersonal->where($queryItems)->where(DB::raw('CONCAT_WS(" ", name, last_name, email, country)'), 'like', $search);

        return SpecialPersonalsResource::collection($per_page == 0 ? $specPerson->get() : $specPerson->paginate($per_page));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreSpecialPersonalRequest $request)
    {

        if (Auth::user()->user_type == 3) {
            return $this->error('', 'Nije Vam dozvoljeno kreirati strucna lica!', 404);
        }
        if (Auth::user()->user_type == 4 && Auth::user()->specialPersonnel != null) {

            return $this->error('', 'Možete kreirati samo jedno stručno lice!', 404);
        }
        $request->validated($request->all());



        $role = $request->role;
        switch ($role) {
            case 0:
                $roleText = 'Uprava';
                break;
            case 2:
                $roleText = 'Trener';
                break;
            case 1:
                $roleText = 'Sudija';
                break;
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

        if ($request->has(['clubId', 'title'])) {
            $club = Auth::user()->user_type == 0 && Auth::user()->club != null ? Auth::user()->club : Club::where('id', $request->clubId)->first();
            $title = $role == 0 || $role == 1 ? $request->title : $roleText;
            $club->roles()->create([
                'special_personals_id' => $special_personal->id,
                'title' => $title,
                'role' => $role
            ]);
        }
        if ($request->has(['componentId', 'title'])) {
            $component = Component::where('id', $request->clubId)->first();
            $title = $request->title;
            $component->roles()->create([
                'special_personals_id' => $special_personal->id,
                'title' => $title,
                'role' => $role
            ]);
        }

        if ($request?->image != null) {
            $tempImage = $request->image;
            $image_name = time() . '_' . $tempImage->getClientOriginalName();
            $storePath = storage_path('app/special-personal-image/') . $image_name;
            $path = 'special-personal-image/' . $image_name;
            ImagesResize::make($tempImage->getRealPath())->resize(500, 500)->save($storePath);
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
    public function show(SpecialPersonal $special_personnel)
    {
        return new SpecialPersonalsResource($special_personnel);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateSpecialPersonalRequest $request, SpecialPersonal $special_personnel)
    {
        $request->validated($request->all());
        if ($request->role != null && Auth::user()->user_type == 0) {
            $role = $special_personnel->role;
        } else {
            $role = $request->role;
        }

        $special_personnel->update($request->except('lastName', 'role', 'status', 'phone'));
        if ($request->has('lastName')) {
            $special_personnel->update([
                'last_name' => $request->lastName
            ]);
        }

        if ($request->has('role')) {
            $special_personnel->update([
                'role' => $role
            ]);
        }
        if ($request->has('phone')) {
            $special_personnel->update([
                'phone_number' => "+$request->phone"
            ]);
        }

        if ($request->has('status')) {
            if (Auth::user()->user_type != 0) {
                $special_personnel->update([
                    'status' => $request->status
                ]);
            }
        }

        return new SpecialPersonalsResource($special_personnel);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(SpecialPersonal $special_personnel)
    {
        if (Auth::user()->user_type != 2) {
            return $this->restricted('', 'Not alowed!', 403);
        }
        foreach ($special_personnel->image()->get() as $image) {
            Storage::delete($image->url);
        }
        foreach ($special_personnel->document()->get() as $document) {
            Storage::delete($document->url);
        }

        $roles = Roles::where('special_personals_id', $special_personnel->id)->get();
        foreach ($roles as $role) {
            $role->delete();
        }

        $special_personnel->image()->delete();
        $special_personnel->document()->delete();
        $special_personnel->delete();
        return $this->success('', 'Uspješno Obrisano!', 200);
    }
}
