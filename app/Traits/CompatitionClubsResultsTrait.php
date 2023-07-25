<?php

namespace App\Traits;

use App\Models\Category;
use App\Models\Club;
use App\Models\Compatition;
use App\Models\CompatitionClubsResults;

trait CompatitionClubsResultsTrait {


    public function calculateResults($compatitionId, $clubIds = [], $calculationType = 'registrations') {
        $compatition = Compatition::where('id', $compatitionId)->first();
        $compatitionResults = $compatition->compatitionClubsResults;
        $compatitionRegistrations = $compatition->registrations;
        
        if($calculationType == 'registrations'){        
            if(empty($clubIds)) {
                foreach($compatitionRegistrations->unique('club_id') as $club) {
                    $noCompatitiors = $compatitionRegistrations->where('club_id', $club->club_id)->unique('compatitor_id')->count();
                    $noTeams = $compatitionRegistrations->where('team_or_single', 0)->where('club_id', $club->club_id)->groupBy('team_id')->count();
                    $noSingles = $compatitionRegistrations->where('team_or_single', 1)->where('club_id', $club->club_id)->count();
                    $totalPrice = $noTeams * $compatition->price_team + $noSingles * $compatition->price_single;
                    $clubsIds[] = $club->club_id;
                    return $club;
                    if($compatitionResults->where('club_id', $club->club_id)->count() == 0) {
                        CompatitionClubsResults::create([
                            'compatition_id' => $compatition->id,
                            'club_id' => $club->club_id
                        ]);
                    }
                    $resultsData = $compatitionResults->where('club_id', $club->club_id)->first();
                    // return $noSingles;
                    $resultsData->update([
                        'no_compatitors' => $noCompatitiors,
                        'no_teams' => $noTeams,
                        'no_singles' => $noSingles,
                        'total_price' => $totalPrice
                    ]);
                }
            
            }
            if(!empty($clubIds)) {
                foreach($clubIds as $clubId) {
                    $noCompatitiors = $compatitionRegistrations->where('club_id', $clubId)->unique('compatitor_id')->count();
                    $noTeams = $compatitionRegistrations->where('team_or_single', 0)->where('club_id', $clubId)->groupBy('team_id')->count();
                    $noSingles = $compatitionRegistrations->where('team_or_single', 1)->where('club_id', $clubId)->count();
                    $totalPrice = $noTeams * $compatition->price_team + $noSingles * $compatition->price_single;
                    if($compatitionResults->where('club_id', $clubId)->count() == 0) {
                        CompatitionClubsResults::create([
                            'compatition_id' => $compatition->id,
                            'club_id' => $clubId
                        ]);
                        continue;
                    }
                    $resultsData = $compatitionResults->where('club_id', $clubId)->first();
                    $resultsData->update([
                        'no_compatitors' => $noCompatitiors,
                        'no_teams' => $noTeams,
                        'no_singles' => $noSingles,
                        'total_price' => $totalPrice
                    ]);
                }
            }
        }
        
        if($calculationType == 'results') {
            if(empty($clubIds)) {
                foreach($compatitionRegistrations->unique('club_id') as $club) {
                    $notOfficialCategories = Category::where('is_official', 0)->get();
                    $noOficials = [];
                    foreach($notOfficialCategories as $category) {
                        $noOfficials[] = $category->id;
                    }
                    
                    $noCompatitiors = $compatitionRegistrations->where('status', 1)->where('club_id', $club->club_id)->unique('compatitor_id')->count();
                    $teams = $compatitionRegistrations->where('team_or_single', 0)->where('club_id', $club->club_id);
                    $singles = $compatitionRegistrations->where('team_or_single', 1)->where('club_id', $club->club_id);
                    $gold = $singles->where('status', 1)->whereNotIn('category_id', $noOficials)->where('position', 3)->count() + $teams->where('position', 3)->groupBy('team_id')->count();
                    $silver = $singles->where('status', 1)->where('position', 2)->count() + $teams->where('position', 2)->groupBy('team_id')->count();
                    $bronze = $singles->where('status', 1)->whereNotIn('category_id', $noOficials)->where('position', 1)->count() + $teams->where('position', 1)->groupBy('team_id')->count();
                    $points = $singles->where('status', 1)->whereNotIn('category_id', $noOficials)->sum('position') + $teams->unique('team_id')->sum('position');
                    $resultsData = $compatitionResults->where('club_id', $club->club_id)->first();
                    // return $noSingles;
                    $resultsData->update([
                        'gold_medals' => $gold,
                        'silver_medals' => $silver,
                        'bronze_medals' => $bronze,
                        'points' => $points
                    ]);
                }
            
            }
            
            if(!empty($clubIds)) {
                
                foreach($clubIds as $clubId) {
                    $notOfficialCategories = Category::where('is_official', 0)->get();
                    $noOficials = [];
                    foreach($notOfficialCategories as $category) {
                        $noOfficials[] = $category->id;
                    }
                    $noCompatitiors = $compatitionRegistrations->where('club_id', $clubId)->unique('compatitor_id')->count();
                    $teams = $compatitionRegistrations->where('team_or_single', 0)->where('club_id', $clubId);
                    $singles = $compatitionRegistrations->where('team_or_single', 1)->where('club_id', $clubId);
                    $gold = $singles->where('status', 1)->whereNotIn('category_id', $noOficials)->where('position', 3)->count() + $teams->where('position', 3)->groupBy('team_id')->count();
                    $silver = $singles->where('status', 1)->whereNotIn('category_id', $noOficials)->where('position', 2)->count() + $teams->where('position', 2)->groupBy('team_id')->count();
                    $bronze = $singles->where('status', 1)->whereNotIn('category_id', $noOficials)->where('position', 1)->count() + $teams->where('position', 1)->groupBy('team_id')->count();
                    
                    $points = $singles->where('status', 1)->whereNotIn('category_id', $noOficials)->sum('position') + $teams->unique('team_id')->sum('position');
                    $resultsData = $compatitionResults->where('club_id', $clubId)->first();
                    $resultsData->update([
                        'gold_medals' => $gold,
                        'silver_medals' => $silver,
                        'bronze_medals' => $bronze,
                        'points' => $points
                    ]);
                    
                }

            }
           
        }
        
    }
}