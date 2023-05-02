<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCompatitorRequest;
use App\Http\Resources\CompatitorsResource;
use App\Models\Compatitor;
use App\Filters\CompatitorsFilter;
use App\Http\Requests\UpdateComatitorRequest;
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
        $sortDirection = $request->sortDirection == null ? 'desc' : $request->sortDirection;
        $compatitior = Compatitor::orderBy($sort, $sortDirection);

        $search = '%'. $request->search . '%';
        if($per_page == 0) {
            return CompatitorsResource::collection($compatitior->where($queryItems)->where(DB::raw('CONCAT_WS(" ", name, last_name)'), 'like', $search)->get());
        }
        return CompatitorsResource::collection($compatitior->where($queryItems)->where(DB::raw('CONCAT_WS(" ", name, last_name)'), 'like', $search)->paginate($per_page));
       
    }

    public function index(Request $request)
    {
        
        $per_page = $request->perPage; 
        $filter = new CompatitorsFilter();
        $queryItems = $filter->transform($request); //[['column', 'operator', 'value']]
        $sort = $request->sort == null ? 'id' : $request->sort;
        $sortDirection = $request->sortDirection == null ? 'desc' : $request->sortDirection;
        $compatitior = Compatitor::orderBy($sort, $sortDirection);

        $search = '%'. $request->search . '%';

        if(Auth::user()->user_type == 0) {
            if($per_page == 0) {
                return CompatitorsResource::collection(
                    $compatitior->where('club_id',  Auth::user()->club->id)->where($queryItems)->where(DB::raw('CONCAT_WS(" ", name, last_name)'), 'like', $search)->get()
                );
            }
            return CompatitorsResource::collection(
                $compatitior->where('club_id',  Auth::user()->club->id)->where($queryItems)->where(DB::raw('CONCAT_WS(" ", name, last_name)'), 'like', $search)->paginate($per_page)
            );
        } else {
            if($per_page == 0) {
                return CompatitorsResource::collection(
                    $compatitior->where($queryItems)->where(DB::raw('CONCAT_WS(" ", name, last_name)'), 'like', $search)->get()
                );
            }
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

        $kscgNo = "100000";

        $request->validated($request->all());
        if(Auth::user()->user_type != 0 && $request->has('clubId') == 0) {
            return $this->error('','Morate unijeti id kluba', 403);
        }
        $compatitor = Compatitor::create([
            'club_id' => Auth::user()->user_type == 0 ? Auth::user()->club->id : $request->clubId,
            'kscg_compatitor_id' => $kscgNo,
            'name' => $request->name,
            'last_name' => $request->lastName,
            'gender' => $request->gender,
            'jmbg' => $request->jmbg,
            'belt_id' => $request->belt,
            'date_of_birth' => $request->dateOfBirth,
            'weight' => $request->weight,
            'country' => $request->country,
            'status' => Auth::user()->user_type == 0 ? 0 : 1
        ]);
        if($compatitor->country == 'Crna Gora') {
            $country = 'MNE';
            $kscgNewNo = $compatitor->kscg_compatitor_id + $compatitor->id;
            $kscgId = $country . substr($kscgNewNo, 1);
            $compatitor->update(['kscg_compatitor_id'=> $kscgId]);
        }
        if($compatitor->country != 'Crna Gora') {
            $world = 'WKF';
            $kscgNewNo = $compatitor->kscg_compatitor_id + $compatitor->id;
            $kscgId = $world . substr($kscgNewNo, 1);
            $compatitor->update(['kscg_compatitor_id'=> $kscgId]);
        }
        
        if($request->has('document')) {
            $docPath = Storage::putFile('compatitors-docs', $request->document);
            $compatitor->document()->create([
                'name' => 'Licni dokument',
                'doc_link' => $docPath
            ]);
    
        }

        if($request->has('image')) {
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
    public function show_public(Compatitor $competitor)
    {
        return new CompatitorsResource($competitor);
    }

    public function show(Compatitor $competitor)
    {

        if(Auth::user()->user_type == 0 && Auth::user()->club->id != $competitor->club_id) {
            return $this->restricted('', 'You are not authorized to make the request!', 403);
        } else {
            return new CompatitorsResource($competitor);
        }

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateComatitorRequest $request, Compatitor $competitor)
    {
        $request->validated($request->all());
        if(Auth::user()->user_type != 2 && Auth::user()->status == 0) {
            return $this->restricted('', 'Suspendovani ste molimo vas da kontaktirate KSCG!', 403);
        }
        if(Auth::user()->user_type == 0 && $competitor->club_id != Auth::user()->club->id) {
            return $this->restricted('', 'Možete vršiti izmjene samo članova Vašeg kluba!', 403);
        }
        
        $competitor->update($request->except(['lastName', 'dateOfBirth', 'clubId', 'status', 'belt']));

        if ($request->has('lastName')) {
            $competitor->update([
                'last_name' => $request->lastName
            ]);
        }
        if ($request->has('dateOfBirth')) {
            $competitor->update([
                'date_of_birth' => $request->dateOfBirth
            ]);
        }
        if ($request->has('belt')) {
            $competitor->update([
                'belt_id' => $request->belt
            ]);
        }
        if ($request->has('clubId')) {

            if(Auth::user()->user_type != 2 || Auth::user()->user_type == 0) {
                return $this->error('', 'Takmičara mogu premjestiti samo aktivni administratori ove platforme!', 403);
            } 

            $competitor->update([
                'club_id' => $request->clubId
            ]);
        }
        if ($request->has('status')) {

            if(Auth::user()->user_type != 2 || Auth::user()->user_type == 0) {
                return $this->error('', 'Status mogu promijeniti aktivni administratori ove platforme!', 403);
            } 

            $competitor->update([
                'status' => $request->status
            ]);
        }
        if(Auth::user()->user_type == 0 && $request->hasAny(['dateOfBirth', 'belt'])) {
            $competitor->update([
                'status' => 0
            ]);
        }
        return new CompatitorsResource($competitor);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Compatitor $competitor)
    {
        if(Auth::user()->user_type != 2){
            $competitor->update(['club_id' => null]);
        }

        foreach($competitor->image()->get() as $image) {
            Storage::delete($image->url);
        }

        foreach($competitor->document()->get() as $document) {
            Storage::delete($document->doc_link);
            
        }
        
        $competitor->image()->delete();
        $competitor->document()->delete();
        $competitor->delete();
        return $this->success('', 'Takmičar je uspješno obrisan!', 200);
    }
}
