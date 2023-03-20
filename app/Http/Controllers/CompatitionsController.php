<?php

namespace App\Http\Controllers;

use App\Filters\CategoriesFilter;
use App\Filters\CompatitionsFilter;
use App\Http\Requests\StoreCompatitionRequest;
use App\Http\Requests\UpdateCompatitionRequest;
use App\Http\Resources\CategoriesResource;
use App\Http\Resources\CCPResource;
use App\Http\Resources\ClubsOnCompatitionResource;
use App\Http\Resources\CompatitionsResource;
use App\Http\Resources\RegistrationsResource;
use App\Models\Category;
use App\Models\Club;
use App\Models\Compatition;
use App\Models\SpecialPersonal;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Http\Request;
use App\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CompatitionsController extends Controller
{
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $filter = new CompatitionsFilter();
        $queryItems = $filter->transform($request); //[['column', 'operator', 'value']]
        $per_page = $request->perPage;
        $sort = $request->sort == null ? 'start_time_date' : $request->sort;
        $sortDirection = $request->sortDirection == null ? 'desc' : $request->sortDirection;
        $compatition = Compatition::orderBy($sort, $sortDirection);

        $search = '%'. $request->search . '%';

        return CompatitionsResource::collection($compatition->where($queryItems)->where(DB::raw('CONCAT_WS(" ", name, country, city, host_name)'), 'like', $search)->paginate($per_page));
    }

    public function public(Request $request)
    {
        $filter = new CompatitionsFilter();
        $queryItems = $filter->transform($request); //[['column', 'operator', 'value']]
        $per_page = $request->perPage;

        $sort = $request->sort == null ? 'start_time_date' : $request->sort;
        $sortDirection = $request->sortDirection == null ? 'desc' : $request->sortDirection;
        $compatition = Compatition::orderBy($sort, $sortDirection);

        $search = '%'. $request->search . '%';
        
        return CompatitionsResource::collection($compatition->where('status', 1)->where($queryItems)->where(DB::raw('CONCAT_WS(" ", name, country, city, host_name)'), 'like', $search)->paginate($per_page));
    }
    

    public function categories(Request $request, Compatition $competition)
    {
        $filter = new CategoriesFilter();
        $queryItems = $filter->transform($request); //[['column', 'operator', 'value']]
        $per_page = $request->perPage;
      
        $search = '%'. $request->search . '%';
        //return response('alo');
   
        return CategoriesResource::collection((new Collection($competition->categories))->paginate($per_page));
    }
    public function piblicCategories(Request $request, Compatition $competition) {
        $filter = new CategoriesFilter();
        $queryItems = $filter->transform($request); //[['column', 'operator', 'value']]
        $per_page = $request->perPage;
       
      
        $search = '%'. $request->search . '%';
        //return response('alo');
        //return $competition->categories;

        return CategoriesResource::collection((new Collection($competition->categories))->paginate($per_page));
    }

    public function piblicRegistrations(Request $request, Compatition $competition) {
        //$filter = new CategoriesFilter();
        //$queryItems = $filter->transform($request); //[['column', 'operator', 'value']]
        
        $per_page = $request->perPage;
        $search = '%'. $request->search . '%';
        //return response('alo');

        return RegistrationsResource::collection((new Collection($competition->registrations))->paginate($per_page));

    }
    public function piblicClubsResults(Request $request, Compatition $competition) 
    {
        $per_page = $request->perPage;
        $clubs = [];
        foreach ($competition->registrations->countBy('club_id') as $club=>$val) {   
            $clubs[] = $club;
        }
        return ClubsOnCompatitionResource::collection(Club::whereIn('id', $clubs)->paginate($per_page));

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreCompatitionRequest $request)
    {
        if(Auth::user()->user_type != 2 && Auth::user()->status == 0) {
            return $this->restricted('', 'Not alowed!', 403);
        }
        $request->validated($request->all());

        if(Auth::user()->user_type != 0 && $request->status != null) {
            $status = $request->status;
        } else {
            $status = false;
        }
        $registrationStatus = $request->has('registrationStatus') ? $request->registrationStatus : true;
        $compatition = Compatition::create([
            'name' => $request->name,
            'host_name' => $request->hostName,
            'start_time_date' => $request->startTimeDate,
            'registration_deadline' => $request->registrationDeadline,
            'price_single' => $request->priceSingle,
            'price_team' => $request->priceTeam,
            'country' => $request->country,
            'city' => $request->city,
            'address' => $request->address,
            'tatami_no' => $request->tatamiNo,
            'status'=> $status,
            'registration_status' => $registrationStatus
        ]);
        if($request->image != null) {
            $path = Storage::putFile('compatition-image', $request->image);
            $compatition->image()->create([
                'url' => $path
            ]);
        };
        if($request->document != null) {
            $path = Storage::putFile('compatition-docs', $request->document);
            $year = date('Y', strtotime($request->startTimeDate));
            $compatition->document()->create([
                'name' => "Bilten $request->name $year",
                'doc_link' => $path
            ]);
        }
        $categories = array_filter(explode(',', $request->categories));
        $compatition->categories()->sync($categories);

        return new CompatitionsResource($compatition);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Compatition $competition)
    {
        return new CompatitionsResource($competition);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateCompatitionRequest $request, Compatition $competition)
    {
        $request->validated($request->all());
        $competition->update($request->except(['hostName', 'startTimeDate', 'registrationDeadline', 'priceSingle', 'priceTeam', 'registrationStatus', 'status']));
        $request->has('hostName') ? $competition->update(['host_name' => $request->hostName]) : null;
        $request->has('startTimeDate') ? $competition->update(['start_time_date' => $request->startTimeDate]) : null;
        $request->has('registrationDeadline') ? $competition->update(['registration_deadline' => $request->registrationDeadline]) : null;
        $request->has('priceSingle') ? $competition->update(['price_single' => $request->priceSingle]) : null;
        $request->has('priceTeam') ? $competition->update(['price_team' => $request->priceTeam]) : null;
        $request->has('registrationStatus') ? $competition->update(['registration_status' => $request->registrationStatus]) : null;
        $request->has('status') && Auth::user()->user_type != 0 ? $competition->update(['status' => $request->status]) : null;

        if($request->has('categories')) {
            $categories = array_filter(explode(',', $request->categories));
            $competition->categories()->sync($categories);
        }

        return new CompatitionsResource($competition);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Compatition $compatition)
    {
        //
    }

    public function specialPersonalOnCompatition(Compatition $competition, Request $request) {

        $specialPersonal = SpecialPersonal::where('id', $request->specPersonId)->first();
        if($specialPersonal->role == 2 && Auth::user()->user_type == 0) {
            $club = '';
        }
        return response("Takmicenje $competition->name" );
    }
}
