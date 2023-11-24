<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;

use App\Filters\CategoriesFilter;
use App\Filters\CompatitionsFilter;
use App\Filters\CompatitorsFilter;
use App\Filters\RegistrationsFilter;
use App\Http\Requests\StoreCompatitionRequest;
use App\Http\Requests\UpdateCompatitionRequest;
use App\Http\Resources\CategoriesResource;
use App\Http\Resources\CompatitionsResource;
use App\Http\Resources\RegistrationsResource;
use App\Models\Category;
use App\Models\Club;
use App\Models\Compatition;
use App\Models\Compatitor;
use App\Models\Registration;
use App\Models\SpecialPersonal;
use Illuminate\Http\Request;
use App\Support\Collection;
use App\Traits\HttpResponses;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use ImagesResize;
class CompatitionsController extends Controller
{
    use HttpResponses;
    
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
        $search = '%'. $request->search . '%';
        $compatition = Compatition::orderBy($sort, $sortDirection)->where($queryItems)->where(DB::raw('CONCAT_WS(" ", name, country, city, host_name)'), 'like', $search);

        if(Auth::user()->user_type == 0) {
            return CompatitionsResource::collection($compatition->where('is_abroad', '!=', '1')->paginate($per_page));
        }
        return CompatitionsResource::collection($compatition->paginate($per_page));
    }

    public function public(Request $request)
    {
        $filter = new CompatitionsFilter();
        $queryItems = $filter->transform($request); //[['column', 'operator', 'value']]
        $per_page = $request->perPage;

        $sort = $request->sort == null ? 'start_time_date' : $request->sort;
        $sortDirection = $request->sortDirection == null ? 'desc' : $request->sortDirection;
        $compatition = Compatition::orderBy($sort, $sortDirection);
        //return $request->sortDirection;
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
        if($per_page == '0') {
            return CategoriesResource::collection((new Collection($competition->categories)));
        }
        return CategoriesResource::collection((new Collection($competition->categories))->paginate($per_page));
        
    }
    public function piblicCategories(Request $request, Compatition $competition) {
        $filter = new CategoriesFilter();
        $queryItems = $filter->transform($request);
        $per_page = $request->perPage;
        $compatitionCategories = $competition->categories;
        $filteredCategories = [];
        $search = '%'. $request->search . '%';
        foreach($compatitionCategories as $category) {
            $filteredCategories[] = $category->id;
        }
        $categories = Category::whereIn('id', $filteredCategories)->where($queryItems)->where(DB::raw('CONCAT_WS(" ", name, category_name)'), 'like', $search);
        if($per_page == '0') {
            return CategoriesResource::collection($categories->get());
        }
        return CategoriesResource::collection($categories->paginate($per_page));
    }

    public function piblicRegistrations(Request $request, Compatition $competition) {
        $filterRegistrations = new RegistrationsFilter();
        $queryItemsRegistrations = $filterRegistrations->transform($request); //[['column', 'operator', 'value']]
        
        $sort = $request->sort == null ? 'category_id' : $request->sort;
        $sortDirection = $request->sortDirection == null ? 'asc' : $request->sortDirection;
        if($request->has('categoryId') && $request->categoryId['eq'] != ''){
            $sort = 'position';
            $sortDirection = 'desc';
        }
        $regResults = Registration::orderBy($sort, $sortDirection)->orderBy('position', 'desc')->where('compatition_id', $competition->id)->where($queryItemsRegistrations);
        $request->has('isPrinted') ? $competitionResoults = $regResults->where('position', '>=', 1)->where('is_printed', $request->isPrinted) : $competitionResoults = $regResults;
        $per_page = $request->perPage;
        $search = '%'. $request->search . '%';
        $searchedCompetitors = [];
        if($request->has('teamOrSingle') && $request->teamOrSingle['eq'] == 0) {
            
            return RegistrationsResource::collection((new Collection($regResults->get()->unique('team_id')))->paginate($per_page));
        }
        
        if($request->has('search') && $request->search != null || $request->has('gender')) {
            $filter = new CompatitorsFilter();
            $queryItems = $filter->transform($request); //[['column', 'operator', 'value']]
            $competitiors = Compatitor::where(DB::raw('CONCAT_WS(" ", name, last_name)'), 'like', $search)->where($queryItems)->get('id');
            foreach($competitiors as $competitor){
                $searchedCompetitors[] = $competitor->id;
            }
        } else {
            return RegistrationsResource::collection($competitionResoults->paginate($per_page));
        }

        return RegistrationsResource::collection($competitionResoults->whereIn('compatitor_id', $searchedCompetitors)->paginate($per_page));


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
        $registrationStatus = $request->has('registrationStatus') ? $request->registrationStatus : 1;
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
            'registration_status' => $registrationStatus,
            'application_limits' => $request->applicationLimits,
            'category_start_point' => $request->categoryStartPoint,
            'is_abroad' => $request->isAbroad,
            'rematch' => $request->rematch,
            'type' => $request->type,
        ]);
        if($request->image != null) {
            $tempImage = $request->image;
            $image_name = time().'_'.$tempImage->getClientOriginalName();
            $storePath = storage_path('app/compatition-image/') . $image_name;
            $path = 'compatition-image/' . $image_name;
            ImagesResize::make($tempImage->getRealPath())->resize(595, 842)->save($storePath);
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
        if($competition->registrations->count() > 0 && $request->hasAny(['isAbroad', 'priceSingle', 'priceTeam','applicationLimits','categoryStartPoint'])) {
            return $this->error('', 'Takmičenje ima prijava pa nije moguće mijenjati neke podatke', 401);
        }
        if(Auth::user()->user_type != 2 || (Auth::user()->user_type == 1 && Auth::user()->status == 0) ) {
            return $this->error('', 'Ove izmjene mogu raditi samo administratori i komisija sa aktivnim statusom!', 401);
        }
        $request->validated($request->all());
        $competition->update($request->except(['hostName', 'startTimeDate', 'registrationDeadline', 'priceSingle', 'priceTeam', 'registrationStatus', 'status', 'document']));
        if($request?->document != null) {
            foreach($competition->document as $document) {
                Storage::delete($document->doc_link);
                $document->delete();
            }            
            $path = Storage::putFile('compatition-docs', $request->document);
            $competition->document()->create([
                'name' => "Bilten $request->name",
                'doc_link' => $path
            ]);
        }
        if($request?->image != null) {
            $tempImage = $request->image;
            $image_name = time().'_'.$tempImage->getClientOriginalName();
            $storePath = storage_path('app/compatition-image/') . $image_name;
            $path = 'compatition-image/' . $image_name;
            ImagesResize::make($tempImage->getRealPath())->resize(595, 842)->save($storePath);
            $competition->image()->create([
                'url' => $path
            ]);
        };
        $request->has('hostName') ? $competition->update(['host_name' => $request->hostName]) : null;
        $request->has('startTimeDate') ? $competition->update(['start_time_date' => $request->startTimeDate]) : null;
        $request->has('registrationDeadline') ? $competition->update(['registration_deadline' => $request->registrationDeadline]) : null;
        $request->has('status') && Auth::user()->user_type != 0 ? $competition->update(['status' => $request->status, 'registration_status' => $request->status]) : null;
        $request->has('rematch')? $competition->update(['rematch' => $request->rematch]) : null;
        $request->has('type')? $competition->update(['type' => $request->type]) : null;
        $request->has('isAbroad')? $competition->update(['is_abroad' => $request->isAbroad]) : null;
        $request->has('tatamiNo')? $competition->update(['tatami_no' => $request->tatamiNo]) : null;
        $request->has('priceSingle') ? $competition->update(['price_single' => $request->priceSingle]) : null;
        $request->has('priceTeam') ? $competition->update(['price_team' => $request->priceTeam]) : null;
        $request->has('applicationLimits')? $competition->update(['application_limits' => $request->applicationLimits]) : null;
        $request->has('categoryStartPoint')? $competition->update(['category_start_point' => $request->categoryStartPoint]) : null;
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
    public function destroy(Compatition $competition, Request $request)
    {

        if($request->forceDelete == true || $competition->registrations()->count() > 0 ){
            //[forceDelete=true]!
            return $this->error('', 'Ovo takmičenje već ima registracije pa nije moguće obrisati!', 403);
        }
        if(Auth::user()->user_type != 2) {
            return $this->error('', 'Nije dozvoljeno brisati takmičenje!', 403);
        }
        $competition->roles()->delete();
        $competition->image()->delete();
        $competition->document()->delete();
        $competition->registrations()->delete();
        $competition->timeTable()->delete();
        $competition->pools()->delete();
        $competition->poolsTeam()->delete();
        $competition->delete();
        return $this->success('', 'Uspješno obrisano takmičenje.');
    }

    public function specialPersonalOnCompatition(Compatition $competition, Request $request) 
    {
        $specialPersonal = SpecialPersonal::where('id', $request->specPersonId)->first();
        $title = $request->has('title') ? $request->title : 'Trener';
        $position = 'osoba';
        switch($specialPersonal->role){
            case 1:
                $position = 'sudija';
                break;
            case 2:
                $position = 'trener';
                break;
        }
    
        
        if($specialPersonal->role == 1 && Auth::user()->user_type == 0) {
            return $this->error('', 'Sudije mogu dodati samo administratori!', 403);
        }
        
        if($competition->roles->where('special_personals_id', $specialPersonal->id)->count() >= 1) {
            return $this->error('', "Ovaj $position je već dodat!", 422);
        }
        if(Auth::user()->user_type == 0 && $specialPersonal->role == 2) {
            $clubName = Auth::user()->club->name;
            $title = "$clubName Trener";
        }
        
        // if(Auth::user()->user_type == 0 && Auth::user()->club != null) {
        //     $clubId = Auth::user()->club;
        //     $clubData = Club::where('id', $clubId->id)->first();
        //     $clubRoles = [];
        //     foreach($clubData->roles as $role){

        //         if($competition->roles->where('special_personals_id', $role->special_personals_id)->first() != null ) {
        //             $clubRoles[] = $role->special_personals_id;
        //         }
        //     }
        //     $foundRoles = $competition->roles->whereIn('special_personals_id', $clubRoles);
        //     foreach($foundRoles as $oneRole) {
        //         $oneRole->delete();
        //     }

        // }

        

        $competition->roles()->create([
            'special_personals_id' => $request->specPersonId,
            'title' => $title,
            'role' => $specialPersonal->role
        ]);
        return $this->success('', "Uspješno dodat $title!");
    }
}
