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
        //return response()->json((boolean)($request->name === 'yearlyMembership'));
        if($request->name == 'yearlyMembership' || $request->name == 'midYearMembership' || $request->name == 'beltsChange') {
            if($request->name == 'yearlyMembership') {
                $today = date('Y', strtotime(now()));
                $allClubsMemberships = ClubMembership::where('club_id', $request->clubId)->where('name', 'yearlyMembership')->get();
                if($allClubsMemberships->count() > 0) {
                    if(date('Y', strtotime($allClubsMemberships->last()->created_at)) == $today) {
                        return $this->error('', "Već imate kreiranu Godišnju članarinu za $today", 404);
                    }
                }
            }
            $clubMembershipPrice = $request->name == 'yearlyMembership' ? 200 : NULL;
            
            $clubsMembership = ClubMembership::create([
                'club_id' => $request->clubId,
                'name' => $request->name,
                'is_paid' => 0,
                'status' => 0,
                'is_submited' => 0,
                'membership_price' => $clubMembershipPrice,
                'amount_to_pay' => $clubMembershipPrice
            ]);
            // foreach($request->competitorsIds as $competitor) {
            //     $competitorData = Compatitor::where('id', $competitor)->first();
            //     $membershipPrice = $request->name != 'beltsChange' ? ($competitorData->first_membership ? 5 : 3) : NULL;
            //     $competitiorMembership = CompetitorMembership::create([
            //         'club_membership_id' => $clubsMembership->id,
            //         'competitor_id' => $competitor,
            //         'membership_price' => $membershipPrice,
            //     ]);
            // }
            // $priceSum = CompetitorMembership::where('club_membership_id', $clubsMembership->id)->sum('membership_price');
            // $clubsMembership->update([
            //     'amount_to_pay' => $priceSum
            // ]);
            return $this->success('', "Uspjesno kreiranje aplikacije za članstvo.");
        }
        return $this->error('', 'Naziv koji ste odabrali ne posjeduje funkcionalnost', 404);
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
        if(Auth::user()->user_type == 0) {
            return $this->error('', 'Klubovi ne mogu da dopune ovaj podatak', 403);
        }
        //return $membership;
        if($membership->name == 'yearlyMembership' || $membership->name == 'midYearMembership') {
            if($request->has('status')) {
                $clubToUpdate = Club::where('id', $membership->club_id)->first();
                $competitorsToUpdate = CompetitorMembership::where('club_membership_id', $membership->id)->get();
                $membership->update([
                    'status' => $request->status
                ]);
                if($membership->name == 'yearlyMembership') {
                    $clubToUpdate->update([
                        'status' => $request->status
                    ]);
                    $clubToUpdate->user->update([
                        'status' => $request->status
                    ]);
                }
                foreach($competitorsToUpdate as $competitorMembership){
                    $competitorMembership->compatitor->update([
                        'status' => $request->status,
                        'first_membership' => 1
                    ]);
                }
                return $this->success('', 'Uspješno promjenjen status');
            }
        } 
        if($request->has('isPaid')) {
            $membership->update([
                'is_paid' => $request->isPaid
            ]);
            return $this->success('', 'Uspjesno evidentirana uplata');
        }
        
        if($membership->name == 'beltsChange') {
            if($request->has('status')) {
                $membership->update([
                    'status' => $request->status
                ]);
                $clubAppliedCompetitors = $membership->competitiorMemberships;
                return $clubAppliedCompetitors;

            }
        }

    }
    public function competitorMembershipAdd(Request $request, ClubMembership $membership) 
    {
        if($membership->name == 'yearlyMembership' || $membership->name == 'midYearMembership') {
            foreach($request->competitors as $competitor) {
                $getCompetitor = Compatitor::where('id', $competitor)->first();
                CompetitorMembership::create([
                    'club_membershi_id' => $membership->id,
                    'competitor_id' => $getCompetitor->id,
                    'membership_price' => $getCompetitor->first_membership ? 3.00 : 5.00,
                ]);
            }
            $getCompetitorMemberships = CompetitorMembership::where('club_membership_id', $membership->id)->get();
            $yearlyMembership = $membership->name == 'yearlyMembership' ? 200.00 : 0;
            $membership->update([
                'amount_to_pay' => $getCompetitorMemberships->sum('membership_price') + $yearlyMembership
            ]);
            return $this->success('', 'Uspješno dodati takmičari');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

}
