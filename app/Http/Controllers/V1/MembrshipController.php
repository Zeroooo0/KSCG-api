<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ClubMembershipResource;
use App\Http\Resources\ClubsResource;
use App\Http\Resources\CompetitorMembershipResource;
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
        if($request->has('isSubmited') && Auth::user()->user_type != 0){
            return ClubMembershipResource::collection(ClubMembership::orderBy('id', 'desc')->where('is_submited', '1')->paginate($request->perPage));
        }
        return ClubMembershipResource::collection(ClubMembership::orderBy('id', 'desc')->paginate($request->perPage));
    }
    public function compatitorsMembership(Request $request, ClubMembership $membership) 
    {

        $competitiorMemberships = CompetitorMembership::orderBy('id', 'desc')->where('club_membership_id', $membership->id);

        return CompetitorMembershipResource::collection($competitiorMemberships->paginate($request->perPage));
      
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
            $name = '';
            if($request->type == 'yearlyMembership') {
                $today = date('Y', strtotime(now()));
                if($request->type == 'yearlyMembership'){
                    $name = "Članarina za $today.";
                }
                $allClubsMemberships = ClubMembership::where('club_id', $request->clubId)->where('type', 'yearlyMembership')->get();
                if($allClubsMemberships->count() > 0) {
                    if(date('Y', strtotime($allClubsMemberships->last()->created_at)) == $today) {
                        return $this->error('', "Već imate kreiranu Godišnju članarinu za $today", 404);
                    }
                }
            }
            $clubMembershipPrice = $request->type == 'yearlyMembership' ? 200 : NULL;
            
            if($request->type == 'midYearMembership'){
                $name = "Registracija članova";
            }
            if($request->type == 'beltsChange'){
                $name = 'Promjena pojaseva';
            }
            ClubMembership::create([
                'club_id' => $request->clubId,
                'type' => $request->type,
                'name' => $name,
                'is_paid' => $request->type == "beltsChange" ? 1 : 0,
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
                    'first_membership' => 0
                ]);
            }
            return $this->success('', 'Uspjesno evidentirana uplata');
        }
        
        if($membership->type == 'beltsChange') {
            if($request->has('status') && $request->status == '1') {
                $membership->update([
                    'status' => $request->status
                ]);
                $clubAppliedCompetitors = $membership->competitorMemberships;
                
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
            $compatitorMember = $request->competitor;
           
            
            $compatitor =  Compatitor::where('id', $compatitorMember)->first();
            $competitorsMemberships = $membership->competitorMemberships->where('competitor_id', $compatitorMember);
            if($competitorsMemberships->count() > 0 ) {
                $input['message'] = "Takmičar $compatitor->name $compatitor->last_name je već prijavljen";
                $errors[] = $input;
            }
            if($compatitor->status) {
                $input['message'] = "Takmičar $compatitor->name $compatitor->last_name je već aktivan!";
                $errors[] = $input;
            }
            
            if(count($errors) == 0) {
                CompetitorMembership::create([
                    'first_membership' => $compatitor->first_membership,
                    'club_membership_id' => $membership->id,
                    'competitor_id' => $compatitor->id,
                    'membership_price' => $compatitor->first_membership ? 5.00 : 3.00,
                    
                ]);
                $getCompetitorMemberships = CompetitorMembership::where('club_membership_id', $membership->id)->get();
                $yearlyMembership = $membership->type == 'yearlyMembership' ? 200.00 : 0;
                $membership->update([
                    'amount_to_pay' => $getCompetitorMemberships->sum('membership_price') + $yearlyMembership
                ]);
                return $this->success('', 'Uspješno dodat takmičar');
            }
            return $this->error('', $errors, 404);
        }
        if($membership->type == 'beltsChange') {
            $errors = [];
            if(Auth::user()->user_type == 0 && $membership->is_submited == 1){
                return $this->error('','Nakon objave nije moguće mijenjati aplikaciju', 404);
            }
            if($request->has('beltId') && $request->beltId == null) {
                return $this->error('', 'Ovaj zahtjev mora da sadrži pojas', 404);
            }
            if($request->has('competitors') ) {
                return $this->error('', 'Morate poslati bar jednog takmicara!', 404);
            }
            $belt = $request->beltId;
            $competitorsMembership = CompetitorMembership::where('club_membership_id', $membership->id);
            $competitorId = $request->competitor;
            
            $competitor = Compatitor::where('id', $competitorId)->first();


            //Checking rules and duplicates
            if(!$request->has('competitor')) {
                $errors['message'] = "Takmičar $competitor->name $competitor->last_name, već je dodat!";                
            } 
            if($competitorsMembership->where('competitor_id', $competitorId)->count() > 0) {
                $errors['message'] = "Takmičar $competitor->name $competitor->last_name, već je dodat!";                
            } 
       
            if($competitor->belt_id >= $belt) {
                $errors['message'] = "Takmičar $competitor->name $competitor->last_name, već posjeduje ili je vec na vecem nivou!";
            } 
            
            if(count($errors) == 0) {
                CompetitorMembership::create([
                    'club_membership_id' => $membership->id,
                    'competitor_id' => $competitorId,
                    'belt_id' => $belt,
                ]);
                return $this->success('', 'Uspješno ste registrovali takmičare!');
            }
            return $this->error('', $errors, 404);
            
        }
        return $this->error('', "Pokusajte kasnije", 404);
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

        
        if($membership->competitorMemberships->count() > 0) {
            CompetitorMembership::where('club_membership_id', $membership->id)->delete();
        }
        $membership->delete();
        return $this->success('', 'Uspješno obrisano');
    }
    public function destroyCompetitorsMembership(CompetitorMembership $membershipCompetitors)
    {
        
        if(Auth::user()->user_type == 0 && $membershipCompetitors->clubMembership->is_submited) {
            return $this->error('', 'Nije moguće obrisati!', 404);
        }

        $membershipCompetitors->delete();
        return $this->success('', 'Uspješno obrisano');
    }

}
