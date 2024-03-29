<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Filters\ClubsFilter;
use App\Http\Requests\StoreClubRequest;
use App\Http\Resources\ClubPublicResource;
use App\Http\Resources\ClubsResource;
use App\Models\Club;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use ImagesResize;

class ClubsController extends Controller
{
    use HttpResponses;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function public(Request $request)
    {
        $filter = new ClubsFilter();
        $queryItems = $filter->transform($request); //[['column', 'operator', 'value']]
        $per_page = $request->perPage;
        $sort = $request->sort == null ? 'id' : $request->sort;
        $sortDirection = $request->sortDirection == null ? 'desc' : $request->sortDirection;
        $club = Club::orderBy($sort, $sortDirection);

        $search = '%' . $request->search . '%';
        if ($per_page != 0) {
            return ClubsResource::collection(
                $club->where($queryItems)->where(DB::raw('CONCAT_WS(" ", name, short_name, country, town)'), 'like', $search)->paginate($per_page)
            );
        }
        return ClubsResource::collection(
            $club->where($queryItems)->where(DB::raw('CONCAT_WS(" ", name, short_name, country, town)'), 'like', $search)->get()
        );
    }

    public function index(Request $request)
    {
        $filter = new ClubsFilter();
        $queryItems = $filter->transform($request); //[['column', 'operator', 'value']]
        $per_page = $request->perPage;
        $sort = $request->sort == null ? 'id' : $request->sort;
        $sortDirection = $request->sortDirection == null ? 'desc' : $request->sortDirection;
        $club = Club::orderBy($sort, $sortDirection);

        $search = '%' . $request->search . '%';

        if (Auth::user()->user_type == 0) {
            return ClubsResource::collection(
                Club::where('user_id', Auth::user()->id)->get()
            );
        } else {
            if ($per_page != 0) {
                return ClubsResource::collection(
                    $club->where($queryItems)->where(DB::raw('CONCAT_WS(" ", name, short_name, country, town)'), 'like', $search)->paginate($per_page)
                );
            }
            return ClubsResource::collection(
                $club->where($queryItems)->where(DB::raw('CONCAT_WS(" ", name, short_name, country, town)'), 'like', $search)->get()
            );
        }
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreClubRequest $request)
    {
        if (Auth::user()->user_type != 2 && Auth::user()->club != null) {
            return $this->restricted('', 'Not alowed!', 403);
        }
        $request->validated($request->all());

        $club = Club::create([
            'user_id' => Auth::user()->user_type == 0 ? Auth::user()->id : $request->userId,
            'name' => $request->name,
            'short_name' => $request->shortName,
            'country' => $request->country,
            'town' => $request->city,
            'address' => $request->address,
            'pib' => $request->pib,
            'email' => $request->email,
            'phone_number' => $request->phoneNumber,
            'status' => 0
        ]);
        if ($request->image != null) {
            $tempImage = $request->image;
            $image_name = time() . '_' . $tempImage->getClientOriginalName();
            $storePath = storage_path('app/club-image/') . $image_name;
            $path = 'club-image/' . $image_name;
            ImagesResize::make($tempImage->getRealPath())->resize(500, 500)->save($storePath);
            $club->image()->create([
                'url' => $path
            ]);
        }

        return new ClubsResource($club);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show_public(Club $club, Request $request)
    {
        return new ClubsResource($club);
    }

    public function show(Club $club)
    {
        if (Auth::user()->user_type == 0) {
            if (Auth::user()->club->id == null) {
                return $this->error('', 'Potrebnoje da kreirate klub ili da vam administrator dodijeli jedan!', 403);
            }
            $user_club = Club::where('user_id', Auth::user()->id)->first();
            return new ClubsResource($user_club);
        } else {
            return new ClubsResource($club);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Club $club)
    {
        /*
        if(Auth::user()->user_type != 2 && Auth::user()->status == 0) {
            return $this->restricted('', 'Not alowed!', 403);
        }
        */
        if (Auth::user()->user_type == 0 && Auth::user()->club->id != $club->id) {
            return $this->restricted('', 'Ovaj korisnik moze mijenjati samo podatke za klub: ' . Auth::user()->club->name, 403);
        }
        $club->update($request->except(['shortName', 'phoneNumber', 'userId', 'status']));

        if (Auth::user()->user_type == 0 && $request->has('country') && $request->country != $club->country) {
            return $this->error('', 'Nije moguću mijenjati državu', 404);
        }
        $request->has('shortName')  ? $club->update(['short_name' => $request->shortName]) : null;
        $request->has('phoneNumber') ? $club->update(['phone_number' => $request->phoneNumber]) : null;
        if ($request->has('userId')) {
            if (Auth::user()->user_type != 0) {
                $club->update([
                    'user_id' => $request->userId
                ]);
            } else {
                return $this->restricted('', 'Not promited to change user id!', 403);
            }
        }

        if ($request->has('status')) {
            $club->update(['status' => $request->status]);
            if ($club->user != null) {
                $club->user->update(['status' => $request->status]);
            }
        }

        return new ClubsResource($club);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    public function destroy(Request $request, Club $club)
    {
        if (Auth::user()->user_type != 2) {
            return $this->restricted('', 'Not alowed!', 403);
        }
        if ($club->compatitors()->count() > 0) {
            if ($request->newClubId == null) {
                return $this->error('', 'Molimo vas da odaberete klub u koji zelite prebaciti takmicare!', 403);
            } else {
                $club->compatitors()->update([
                    'club_id' => $request->newClubId,
                ]);
            }
        }

        $club->roles()->delete();
        $club->image()->delete();
        $club->delete();

        return $this->success('', 'Klub je uspjesno obrisan.');
    }
}
