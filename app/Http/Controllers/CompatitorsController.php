<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCompatitorRequest;
use App\Http\Resources\CompatitorsResource;
use App\Models\Compatitor;
use App\Filters\CompatitorsFilter;
use App\Http\Resources\CompatitorsCollectionResource;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CompatitorsController extends Controller
{
    use HttpResponses;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    
    public function public(Request $request)
    {
        $filter = new CompatitorsFilter();
        $queryItems = $filter->transform($request); //[['column', 'operator', 'value']]
        $per_page = $request->perPage;
        $sort = $request->sort == null ? 'id' : $request->sort;
        $sortDirection = $request->sortDirection == null ? 'asc' : $request->sortDirection;
        $compatitior = Compatitor::orderBy($sort, $sortDirection);

        $search = '%'. $request->search . '%';

        return CompatitorsResource::collection($compatitior->where($queryItems)->where(DB::raw('CONCAT_WS(" ", name, last_name)'), 'like', $search)->paginate($per_page));
       
    }

    public function index(Request $request)
    {
        
        $per_page = $request->perPage; 
        $filter = new CompatitorsFilter();
        $queryItems = $filter->transform($request); //[['column', 'operator', 'value']]
        $sort = $request->sort == null ? 'id' : $request->sort;
        $sortDirection = $request->sortDirection == null ? 'asc' : $request->sortDirection;
        $compatitior = Compatitor::orderBy($sort, $sortDirection);

        $search = '%'. $request->search . '%';

        if(Auth::user()->user_type == 0) {
            return CompatitorsResource::collection(
                $compatitior->where('club_id',  Auth::user()->club->id)->where($queryItems)->where(DB::raw('CONCAT_WS(" ", name, last_name)'), 'like', $search)->paginate($per_page)
            );
        } else {
            return CompatitorsResource::collection(
                $compatitior->where($queryItems)->where(DB::raw('CONCAT_WS(" ", name, last_name)'), 'like', $search)->paginate($per_page)
            );
        }
  

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreCompatitorRequest $request)
    {
        if(Auth::user()->user_type !== 2 && Auth::user()->status == 0) {
            return $this->restricted('', 'Not alowed!', 403);
        }
        $request->validated($request->all());
        
        $compatitor = Compatitor::create([
            'club_id' => Auth::user()->user_type == 0 ? Auth::user()->club->id : $request->clubId,
            'kscg_compatitor_id' => $request->kscgId,
            'name' => $request->name,
            'last_name' => $request->lastName,
            'gender' => $request->gender,
            'jmbg' => $request->jmbg,
            'belt_id' => $request->belt,
            'date_of_birth' => $request->dateOfBirth,
            'weight' => $request->weight,
            'status' => Auth::user()->user_type == 0 ? 0 : 1
        ]);
        if($request->image !== null) {
            $path = Storage::putFile('compatitor-image', $request->image);
            $compatitor->image()->create([
                'url' => $path
            ]);
        };


        return new CompatitorsResource($compatitor);
       
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show_public(Compatitor $compatitor)
    {
        return new CompatitorsResource($compatitor);
    }

    public function show(Compatitor $compatitor)
    {

        if(Auth::user()->user_type == 0 && Auth::user()->club->id !== $compatitor->club_id) {
            return $this->restricted('', 'You are not authorized to make the request!', 403);
        } else {
            return new CompatitorsResource($compatitor);
        }

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Compatitor $compatitor)
    {
        
        if(Auth::user()->user_type !== 2 && Auth::user()->status == 0) {
            return $this->restricted('', 'Suspendovani ste molimo vas da kontaktirate KSCG!', 403);
        }
        if(Auth::user()->user_type == 0 && $compatitor->club_id !== Auth::user()->club->id) {
            return $this->restricted('', 'Možete vršiti izmjene samo članova Vašeg kluba!', 403);
        }
        $compatitor->update($request->except(['lastName', 'dateOfBirth', 'clubId', 'status']));


        if ($request->has('lastName')) {
            $compatitor->update([
                'last_name' => $request->lastName
            ]);
        }
        if ($request->has('dateOfBirth')) {
            $compatitor->update([
                'date_of_birth' => $request->dateOfBirth
            ]);
        }
        if ($request->has('clubId')) {

            if(Auth::user()->user_type !== 2 || Auth::user()->user_type == 0) {
                return $this->error('', 'Takmičara mogu premjestiti samo aktivni administratori ove platforme!', 403);
            } 

            $compatitor->update([
                'club_id' => $request->clubId
            ]);
        }
        if ($request->has('status')) {

            if(Auth::user()->user_type !== 2 || Auth::user()->user_type == 0) {
                return $this->error('', 'Status mogu promijeniti aktivni administratori ove platforme!', 403);
            } 

            $compatitor->update([
                'status' => $request->status
            ]);
        }
        if(Auth::user()->user_type == 0 && $request->hasAny(['dateOfBirth', 'weight'])) {
            $compatitor->update([
                'status' => 0
            ]);
        }

        return new CompatitorsResource($compatitor);
 
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Compatitor $compatitor)
    {
        if(Auth::user()->user_type !== 2){
            return $this->restricted('', 'Brisanje nije dozvoljeno!', 403);
        }
        foreach($compatitor->image()->get() as $image) {
            Storage::delete($image->url);
        }
        foreach($compatitor->document()->get() as $document) {
            Storage::delete($document->url);
        }
        $compatitor->image()->delete();
        $compatitor->document()->delete();
        $compatitor->delete();
        return $this->success('', 'Takmičar je uspješno obrisan!', 200);
    }



}
