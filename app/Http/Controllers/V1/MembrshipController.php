<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ClubMembershipResource;
use App\Http\Resources\ClubsResource;
use App\Models\Club;
use App\Models\ClubMembership;
use App\Models\Compatitor;
use App\Models\CompetitorMembership;
use App\Traits\HttpResponses;
use GrahamCampbell\ResultType\Success;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use SebastianBergmann\Comparator\Comparator;

class MembrshipController extends Controller
{
    use HttpResponses;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if(Auth::user()->user_type == 0) {
            $club = Auth::user()->club->id;
            return ClubMembershipResource::collection(ClubMembership::orderBy('id', 'desc')->where('club_id', $club)->paginate($request->perPage));
        }
        return ClubMembershipResource::collection(ClubMembership::orderBy('id', 'desc')->paginate($request->perPage));
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {   
        
        if($request->type == 'yearlyMembership' || $request->type == 'midYearMembership' || $request->type == 'beltsChange') {
            if($request->type == 'yearlyMembership') {
                $today = date('Y', strtotime(now()));
                $allClubsMemberships = ClubMembership::where('club_id', $request->clubId)->where('name', 'yearlyMembership')->get();
                if($allClubsMemberships->count() > 0) {
                    if(date('Y', strtotime($allClubsMemberships->last()->created_at)) == $today) {
                        return $this->error('', "Već imate kreiranu Godišnju članarinu za $today", 404);
                    }
                }
            }
            $clubMembershipPrice = $request->type == 'yearlyMembership' ? 200 : NULL;
            
            ClubMembership::create([
                'club_id' => $request->clubId,
                'type' => $request->type,
                'is_paid' => 0,
                'status' => 0,
                'is_submited' => 0,
                'membership_price' => $clubMembershipPrice,
                'amount_to_pay' => $clubMembershipPrice
            ]);
            return $this->success('', "Uspjesno kreiranje aplikacije za članstvo.");
        }

        return $this->error('', 'Tip koji ste odabrali ne posjeduje funkcionalnost', 404);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(ClubMembership $membership)
    { 
        return new ClubMembershipResource($membership);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ClubMembership $membership)
    {
        if($request->has('isSubmited')) {
            $membership->update(['is_submited' => $request->isSubmited]);
            return $this->success('', 'Uspjesno ste sabmitovali aplikaciju!');
        }
        if(Auth::user()->user_type == 0) {
            return $this->error('', 'Klubovi ne mogu da dopune ovaj podatak', 404);
        }
        if(Auth::user()->user_type != 0 && $membership->is_submited == 0) {
            return $this->error('', 'Klub jos nije zavrsio aplikaciju.', 404);
        }
        
        $competitorsToUpdate = CompetitorMembership::where('club_membership_id', $membership->id)->get();
        //return $membership;
        if($membership->type == 'yearlyMembership' || $membership->type == 'midYearMembership') {
            if($request->has('status')) {
                $clubToUpdate = Club::where('id', $membership->club_id)->first();
                $membership->update([
                    'status' => $request->status
                ]);
                if($membership->type == 'yearlyMembership') {
                    $clubToUpdate->update([
                        'status' => $request->status
                    ]);
                    $clubToUpdate->user->update([
                        'status' => $request->status
                    ]);
                }
                foreach($competitorsToUpdate as $competitorMembership){
                    $competitor = Compatitor::where('id', $competitorMembership->competitor_id)->first();
                    $competitor ->update([
                        'status' => $request->status
                    ]);
                }
                return $this->success('', 'Uspješno promjenjen status');
            }
        } 
        if($request->has('isPaid')) {
            $membership->update([
                'is_paid' => $request->isPaid
            ]);
            foreach($competitorsToUpdate as $competitorMembership){
                $competitor = Compatitor::where('id', $competitorMembership->competitor_id)->first();
                $competitor->update([
                    'first_membership' => 1
                ]);
            }
            return $this->success('', 'Uspjesno evidentirana uplata');
        }
        
        if($membership->type == 'beltsChange') {
            if($request->has('status') && $request->status == '1') {
                $membership->update([
                    'status' => $request->status
                ]);
                $clubAppliedCompetitors = $membership->competitiorMemberships;
                
                foreach($clubAppliedCompetitors as $competitorMember){
                    $competitor = Compatitor::where('id', $competitorMember->competitor_id)->first();
                    $competitor->update([
                        'belt_id' => $competitorMember->belt_id
                    ]);
                }
                return $this->success('', 'Uspješno ste promjenili pojaseve takmičarima.');
            }
           
        }
        return $this->error('', 'Podaci koje šaljete su netacni.', 404);
    }
    public function competitorMembershipAdd(Request $request, ClubMembership $membership) 
    {
        if($membership->is_submited) {
            return $this->error('', 'Kreirajte novu aplikaciju ova je zatvorena!', 404);
        }
        if($membership->type == 'yearlyMembership' || $membership->type == 'midYearMembership') {
            $errors = [];
            $processedData = [];

            foreach($request->competitors as $compatitorMember) {
                $compatitor =  Compatitor::where('id', $compatitorMember)->first();
                $competitorsMemberships = $membership->competitiorMemberships->whereIn('competitor_id', $compatitorMember);
                if($competitorsMemberships->count() > 0 ) {
                    $input['message'] = "Takmičar $compatitor->name $compatitor->last_name je već prijavljen";
                    $errors[] = $input;
                }
                if($compatitor->status) {
                    $input['message'] = "Takmičar $compatitor->name $compatitor->last_name je već aktivan!";
                    $errors[] = $input;
                }
                
                $data['club_membership_id'] = $membership->id;
                $data['competitor_id'] = $compatitor->id;
                $data['membership_price'] = $compatitor->first_membership ? 3.00 : 5.00;
                $data['created_at'] = now();
                $data['updated_at'] = now();
                 
                $processedData[] = $data;
            }
                
            
            if(count($errors) == 0) {
                CompetitorMembership::insert($processedData);
                $getCompetitorMemberships = CompetitorMembership::where('club_membership_id', $membership->id)->get();
                $yearlyMembership = $membership->type == 'yearlyMembership' ? 200.00 : 0;
                $membership->update([
                    'amount_to_pay' => $getCompetitorMemberships->sum('membership_price') + $yearlyMembership
                ]);
                return $this->success('', 'Uspješno dodati takmičari');
            }
            return $this->error('', $errors, 404);
        }
        if($membership->type == 'beltsChange') {
            $errors = [];
            $processedData = [];
            if(Auth::user()->user_type == 0 && $membership->is_submited == 1){
                return $this->error('','Nakon objave nije moguće mijenjati aplikaciju', 404);
            }
            if(!$request->has('beltId') && $request->beltId == null) {
                return $this->error('', 'Ovaj zahtjev mora da sadrži pojas', 404);
            }
            if(!$request->has('competitors') && count($request->competitors) == 0) {
                return $this->error('', 'Morate poslati bar jednog takmicara!', 404);
            }
            $belt = $request->beltId;
            $competitorsMembership = CompetitorMembership::where('club_membership_id', $membership->id);
            foreach($request->competitors as $competitorId) {
                $competitor = Compatitor::where('id', $competitorId)->first();
                $input['club_membership_id'] = $membership->id;
                $input['competitor_id'] = $competitorId;
                $input['belt_id'] = $belt;
                $input['created_at'] = now();
                $input['updated_at'] = now();
                $processedData[] = $input;
                //Checking rules and duplicates
                if($competitorsMembership->where('competitor_id', $competitorId)->count() > 0) {
                    $errors['message'] = "Takmičar $competitor->name $competitor->last_name, već je dodat!";
                   
                } 
                if($competitor->belt_id == $belt) {
                    $errors['message'] = "Takmičar $competitor->name $competitor->last_name, već posjeduje ovaj pojas!";
                } 
            }
            if(count($errors) == 0) {
                CompetitorMembership::insert($processedData);
                return $this->success('', 'Uspješno ste registrovali takmičare!');
            }
            return $this->error('', $errors, 404);
            
        }
        return 'neuspjesno';
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(ClubMembership $membership)
    {
        if(Auth::user()->user_type == 0 && $membership->is_submited) {
            return $this->error('', 'Nije moguće obrisati aplikaciju koja je poslata administratoru!', 404);
        }

        $membership->competitiorMemberships->delete();
        $membership->delete();
        return $this->success('', 'Uspješno obrisano');
    }
    public function destroyCompetitorsMembership(CompetitorMembership $competitorMembership)
    {
        if(Auth::user()->user_type == 0 && $competitorMembership->clubMemberships->is_submited) {
            return $this->error('', 'Nije moguće obrisati!', 404);
        }
        $competitorMembership->delete();
        return $this->success('', 'Uspješno obrisano');
    }

}
