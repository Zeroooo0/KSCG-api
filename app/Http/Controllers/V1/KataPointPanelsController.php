<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Models\KataPointPanel;
use App\Models\Pool;
use App\Models\PoolTeam;
use App\Models\Registration;
use App\Models\TimeTable;
use App\Traits\CompatitionClubsResultsTrait;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;

class KataPointPanelsController extends Controller
{
    use HttpResponses;
    use CompatitionClubsResultsTrait;

    public function store(Request $request, TimeTable $timeTable)
    {
        $isSingle = $timeTable->category->solo_or_team;
        $request->validate([
            'poolId' => ['required_with:registrationId', 'integer'],
            'registrationId' => ['required_with:poolId', 'integer'],
            'judge' => ['required', 'numeric', 'digits_between:1,7'],
            'points' => ['required', 'numeric', 'between:0.00,10.00'],
        ]);
        
        $filterExistedData = $isSingle ? 
            KataPointPanel::where('pool_id', $request->poolId)->where('registration_id', $request->registrationId)->where('judge', $request->judge)->first() :
            KataPointPanel::where('pool_team_id', $request->poolId)->where('team_id', $request->registrationId)->where('judge', $request->judge)->first();
        if($filterExistedData != null) {
            $filterExistedData->update(['points' => $request->points]);
        } else {
            $katePointsPanel = $isSingle ? 
                KataPointPanel::create([
                    'pool_id' => $request->poolId,
                    'registration_id' => $request->registrationId,
                    'judge' => $request->judge,
                    'points' =>$request->points
                ]):
                KataPointPanel::create([
                    'pool_team_id' => $request->poolId,
                    'team_id' => $request->registrationId,
                    'judge' => $request->judge,
                    'points' =>$request->points
                ]);
        }
        $pointsToCalculate = $isSingle ?  
            KataPointPanel::where('pool_id', $request->poolId)->where('registration_id', $request->registrationId)->get() :
            KataPointPanel::where('pool_team_id', $request->poolId)->where('team_id', $request->registrationId)->get();
        $poolRegOne = $isSingle ? 
            Pool::where('id', $request->poolId)->where('registration_one', $request->registrationId)->first() :
            PoolTeam::where('id', $request->poolId)->where('team_one', $request->registrationId)->first();
        $poolRegTwo = $isSingle ? 
            Pool::where('id', $request->poolId)->where('registration_two', $request->registrationId)->first() :
            PoolTeam::where('id', $request->poolId)->where('team_two', $request->registrationId)->first();
        
        $points = [];
        foreach($pointsToCalculate as $judge){
            $points[] = $judge->points;
        }
        sort($points);
        array_shift($points);
        array_pop($points);
        if($isSingle && $poolRegOne != null) {
            $poolRegOne->update(['points_reg_one' => array_sum($points)]);
        } 
        if(!$isSingle && $poolRegOne != null) {
            $poolRegOne->update(['points_team_one' => array_sum($points)]);
        } 
        if($isSingle && $poolRegTwo != null) {
            $poolRegTwo->update(['points_reg_two' => array_sum($points)]);
        } 
        if(!$isSingle && $poolRegTwo != null) {
            $poolRegTwo->update(['points_team_two' => array_sum($points)]);
        } 
        return $this->success('', 'Uspjesno!');
       
    }
    public function makeNextRound(Request $request, TimeTable $timeTable)
    {
        $category = $timeTable->category;
        $compatition = $timeTable->compatition;
        $isSingle = $category->solo_or_team == 1;
        $firstPool =  $isSingle ? Pool::where('compatition_id', $compatition->id)->where('category_id', $category->id)->where('pool', 1)->orderBy('points_reg_one', 'desc')->first() : PoolTeam::where('compatition_id', $compatition->id)->where('category_id', $category->id)->where('pool', 1)->orderBy('points_team_one', 'desc')->first();
        $requestedPool = $isSingle ? Pool::where('compatition_id', $compatition->id)->where('category_id', $category->id)->where('pool', $request->pool)->orderBy('points_reg_one', 'desc')->get() : PoolTeam::where('compatition_id', $compatition->id)->where('category_id', $category->id)->where('pool', $request->pool)->orderBy('points_team_one', 'desc')->get();
        $registeredAll = Registration::where('compatition_id', $compatition->id)->where('category_id', $category->id)->get();
        $clubsArray = [];
        if($firstPool->pool_type == 'KRG3') {

            foreach($requestedPool as $key => $pool) {
                $position = null;
                $poolFor = $isSingle ? $pool->points_reg_one : $pool->points_team_one;
                switch($key) {
                    case 0:
                        $position = $poolFor != 0 ? 3 : null;
                        break;
                    case 1:
                        $position = $poolFor != 0 ? 2 : null;
                        break;
                    case 2:
                        $position = $poolFor != 0 ? 1 : null;
                        break;
                    case 3:
                        $position = $poolFor != 0 ? "0.9" : null;
                        break;
                    default:
                        $position = null;
                        break;
                }

                
                if($isSingle) {
                    $regWin = $registeredAll->where('id', $pool->registration_one)->first();
                    $regWin->update(['position' => $position, 'status' => 1]);
                    $clubsArray[] = $regWin->club_id;

                }
                if(!$isSingle) {
                    $teamsRegs = $registeredAll->where('team_id', $pool->team_one);
                    foreach($teamsRegs as $reg) {
                        $reg->update(['position' => $position, 'status' => 1]);
                        $clubsArray[] = $reg->club_id;
                    }
                }
            }
            
        }
        if($firstPool->pool_type == 'KRG4') {
            foreach($requestedPool as $key => $pool) {
                $position = null;
                $finalsToUpdate = $isSingle ?
                    Pool::where('compatition_id', $compatition->id)->where('category_id', $category->id)->where('pool', 2)->first() :
                    PoolTeam::where('compatition_id', $compatition->id)->where('category_id', $category->id)->where('pool', 2)->first();
                switch($key) {
                    case 1:
                        $isSingle ? 
                            $finalsToUpdate->update(['registration_one', $pool->registration_one]) :
                            $finalsToUpdate->update(['team_one', $pool->team_one]);
                        break;
                    case 2:
                        $isSingle ? 
                            $finalsToUpdate->update(['registration_two', $pool->registration_two]) :
                            $finalsToUpdate->update(['team_two', $pool->team_two]);
                    case 3:
                        $position = 1;
                        break;
                    case 4:
                        $position = 1;
                        break;
                }
                $isSingle ? $registeredAll->where('id', $pool->registration_one)->first()->update(['position' => $position, 'status' => 1]) : null;
                $isSingle ? $clubsArray[] = $registeredAll->where('id', $pool->registration_one)->first()->club_id : null;
                if(!$isSingle) {
                    $teamsRegs = $registeredAll->where('team_id', $pool->team_one);
                    foreach($teamsRegs as $reg) {
                        $reg->update(['position' => $position, 'status' => 1]);
                        $clubsArray[] = $reg->club_id;
                    }
                }
            }
        }
        $arrValidation = ['KRG10', 'KRG24', 'KRG48', 'KRG96', 'KRG192', 'KRG4'];
        if(in_array($firstPool->pool_type, $arrValidation)) {
            $nextGroup = $isSingle ? 
                Pool::where('compatition_id', $compatition->id)->where('category_id', $category->id)->where('pool', $request->pool + 1)->where('pool_type', 'KRGA')->orderBy('id', 'asc')->get() : 
                PoolTeam::where('compatition_id', $compatition->id)->where('category_id', $category->id)->where('pool', $request->pool + 1)->where('pool_type', 'KRGA')->orderBy('id', 'asc')->get();
            $shouldUpdateFinalePool = $nextGroup->count() == 0 ? true : false;
            // if($shouldUpdateFinalePool) {
            //     $finalPoolToUpdate = $isSingle ? 
            //         Pool::
            // }
            $finalesUpdate = false;
            $groupUpdate = false;
            $bronzePosition = false;
            $goldPositions = false;
            switch($requestedPool->first()->pool_type) {
                case 'KRG4':
                    $goldPositions = true;
                    break;
                case 'KRG10':
                    $finalesUpdate = true;
                    break;
                case in_array($requestedPool->first()->pool_type, $arrValidation):
                    $groupUpdate = true;
                    break;
                case 'KRGA':
                    $groupUpdate = !$shouldUpdateFinalePool;
                    $finalesUpdate = $shouldUpdateFinalePool;
                    break;
                case 'KRSF':
                    $bronzePosition = true;
                    break;
            }
            if($requestedPool->whereIn('pool_type', ['KRFM', 'KRSF'])->count() > 0) {
                $goldPositions = true;
                $bronzePosition = true;
            }
            
            if($finalesUpdate) {
                $finalMatchToUpdate = $isSingle ?
                    Pool::where('compatition_id', $compatition->id)->where('category_id', $category->id)->where('pool_type', 'KRFM')->first() :
                    PoolTeam::where('compatition_id', $compatition->id)->where('category_id', $category->id)->where('pool_type', 'KRFM')->first();
                $semiFinalGroupOne = $isSingle ?
                    Pool::where('compatition_id', $compatition->id)->where('category_id', $category->id)->where('group', 1)->where('pool_type', 'KRSF')->first() :
                    PoolTeam::where('compatition_id', $compatition->id)->where('category_id', $category->id)->where('group', 1)->where('pool_type', 'KRSF')->first();
                $semiFinalGroupTwo = $isSingle ?
                    Pool::where('compatition_id', $compatition->id)->where('category_id', $category->id)->where('group', 2)->where('pool_type', 'KRSF')->first() :
                    PoolTeam::where('compatition_id', $compatition->id)->where('category_id', $category->id)->where('group', 2)->where('pool_type', 'KRSF')->first();
                foreach($requestedPool->where('group', 1)->values() as $positon => $pool) {
                    //return $pool;
                    switch($positon) {
                        case 0:
                            $isSingle ? 
                                $finalMatchToUpdate->update(['registration_one' => $pool->registration_one]) : 
                                $finalMatchToUpdate->update(['team_one' => $pool->team_one]);
                            break;
                        case 1:
                            $isSingle ? 
                                $semiFinalGroupOne->update(['registration_one' => $pool->registration_one]) : 
                                $semiFinalGroupOne->update(['team_one' => $pool->team_one]);
                            break;
                        case 2:
                            $isSingle ? 
                                $semiFinalGroupTwo->update(['registration_two' => $pool->registration_one]) : 
                                $semiFinalGroupTwo->update(['team_two' => $pool->team_one]);
                            break;
                    }
                }
                foreach($requestedPool->where('group', 2)->values() as $positon => $pool) {
                    switch($positon) {
                        case 0:
                            
                            $isSingle ? 
                                $finalMatchToUpdate->update(['registration_two' => $pool->registration_one]) : 
                                $finalMatchToUpdate->update(['team_two' => $pool->team_one]);
                            break;
                        case 1:
                            $isSingle ? 
                                $semiFinalGroupOne->update(['registration_two' => $pool->registration_one]) : 
                                $semiFinalGroupOne->update(['team_two' => $pool->team_one]);
                            break;
                        case 2:
                            $isSingle ? 
                                $semiFinalGroupTwo->update(['registration_one' => $pool->registration_one]) : 
                                $semiFinalGroupTwo->update(['team_one' => $pool->team_one]);
                            break;
                    }
                }
            }
            if($groupUpdate) {
                $listForNextRondGOne = [];
                $listForNextRondGTwo = [];
                foreach($requestedPool->where('group', 1)->values() as $positon => $pool) {
                    $position = null;
                    $poolFor = $isSingle ? $pool->points_reg_one : $pool->points_team_one;
                    $poolId = $isSingle ? $pool->registration_one : $pool->team_one;
                    switch($positon) {
                        case 0:
                            $poolFor != 0 ? $listForNextRondGOne[] = $poolId : null;
                            break;
                        case 1:
                            $poolFor != 0 ? $listForNextRondGOne[] = $poolId : null;
                            break;
                        case 2:
                            $poolFor != 0 ? $listForNextRondGOne[] = $poolId : null;
                            break;
                        case 3:
                            $poolFor != 0 ? $listForNextRondGOne[] = $poolId : null;
                            break;
                    }
                }
                foreach($requestedPool->where('group', 2)->values() as $positon => $pool) {
                    $position = null;
                    $poolFor = $isSingle ? $pool->points_reg_one : $pool->points_team_one;
                    $poolId = $isSingle ? $pool->registration_one : $pool->team_one;
                    switch($positon) {
                        case 0:
                            $poolFor != 0 ? $listForNextRondGTwo[] = $poolId : null;
                            break;
                        case 1:
                            $poolFor != 0 ? $listForNextRondGTwo[] = $poolId : null;
                            break;
                        case 2:
                            $poolFor != 0 ? $listForNextRondGTwo[] = $poolId : null;
                            break;
                        case 3:
                            $poolFor != 0 ? $listForNextRondGTwo[] = $poolId : null;
                            break;
                    }
                }
                foreach($nextGroup->where('group', 1)->values() as $key => $newPool) {
                    $isSingle ? $newPool->update(['registration_one' => $listForNextRondGOne[$key]]) :  $newPool->update(['team_one' => $listForNextRondGOne[$key]]);
                }
                foreach($nextGroup->where('group', 2)->values() as $key => $newPool) {
                    $isSingle ? $newPool->update(['registration_one' => $listForNextRondGTwo[$key]]) :  $newPool->update(['team_one' => $listForNextRondGTwo[$key]]);
                }
                
            }
            if($bronzePosition) {
                if($requestedPool->where('group', 1)->values()->count() != 0) {
                    $resultsGOne = [];
                    $inputOne['points'] = $isSingle ? $requestedPool->where('group', 1)->first()->points_reg_one : $requestedPool->where('group', 1)->first()->points_team_one;
                    $inputOne['id'] = $isSingle ? $requestedPool->where('group', 1)->first()->registration_one : $requestedPool->where('group', 1)->first()->team_one;
                    $resultsGOne[] = $inputOne;
                    $inputTwo['points'] = $isSingle ? $requestedPool->where('group', 1)->first()->points_reg_two : $requestedPool->where('group', 1)->first()->points_team_two;
                    $inputTwo['id'] = $isSingle ? $requestedPool->where('group', 1)->first()->registration_two : $requestedPool->where('group', 1)->first()->team_two;
                    $resultsGOne[] = $inputTwo;
                    $unsortedCollection = collect($resultsGOne);

                    foreach($unsortedCollection->sortByDesc('points')->values() as $positon => $regFromPool){
                        $newPosition = $regFromPool['points'] != 0 ? 1 : null;
                        if($positon == 0 && $regFromPool['id'] != null) {
                            if($isSingle) {
                                $registrationWin = Registration::where('id', $regFromPool['id'])->first();
                                $registrationWin->update(['position' => $newPosition, 'status' => 1]);
                                $clubsArray[] = $registrationWin->club_id;
                            }
                            if(!$isSingle) {
                                $registrationsToUpdate = Registration::where('team_id', $regFromPool['id'])->get();
                                foreach($registrationsToUpdate as $reg) {
                                    $reg->update(['position' => $newPosition, 'status' => 1]);
                                    $clubsArray[] = $reg->club_id;
                                }
                            }
                        }
                        if($positon == 1 && $regFromPool['id'] != null) {
                            $newPositionForth = $regFromPool['points'] != 0 ? 0.9 : null;
                            if($isSingle) {
                                $registrationLose = Registration::where('id', $regFromPool['id'])->first();
                                $registrationLose->update(['position' => $newPositionForth]);
                                $clubsArray[] = $registrationLose->club_id;
                            }
                            if(!$isSingle) {
                                $registrationsToUpdate = Registration::where('team_id', $regFromPool['id'])->get();
                                foreach($registrationsToUpdate as $reg) {
                                    $reg->update(['position' => $newPositionForth]);
                                    $clubsArray[] = $reg->club_id;
                                }
                            }
                        }
                    }
                }
                if($requestedPool->where('group', 2)->values()->count() != 0) {
                    $resultsGTwo = [];
                    $inputOne['points'] = $isSingle ? $requestedPool->where('group', 2)->first()->points_reg_one : $requestedPool->where('group', 2)->first()->points_team_one;
                    $inputOne['id'] = $isSingle ? $requestedPool->where('group', 2)->first()->registration_one : $requestedPool->where('group', 2)->first()->team_one;
                    $resultsGTwo[] = $inputOne;
                    $inputTwo['points'] = $isSingle ? $requestedPool->where('group', 2)->first()->points_reg_two : $requestedPool->where('group', 2)->first()->points_team_two;
                    $inputTwo['id'] = $isSingle ? $requestedPool->where('group', 2)->first()->registration_two : $requestedPool->where('group', 2)->first()->team_two;
                    $resultsGTwo[] = $inputTwo;
                    $unsortedCollection = collect($resultsGTwo);
                    foreach($unsortedCollection->sortByDesc('points')->values() as $positon => $regFromPool){
                        $newPosition = $regFromPool['points'] != 0 ? 1 : null;
                        if($positon == 0 && $regFromPool['id'] != null) {
                            if($isSingle) {
                                $registrationWin = Registration::where('id', $regFromPool['id'])->first();
                                $registrationWin->update(['position' => $newPosition, 'status' => 1]);
                                $clubsArray[] = $registrationWin->club_id;
                            }
                            if(!$isSingle) {
                                $registrationsToUpdate = Registration::where('team_id', $regFromPool['id'])->get();
                                foreach($registrationsToUpdate as $reg) {
                                    $reg->update(['position' => $newPosition, 'status' => 1]);
                                    $clubsArray[] = $reg->club_id;
                                }
                            }
                            
                        }
                        if($positon == 1 && $regFromPool['id'] != null) {
                            $newPositionForth = $regFromPool['points'] != 0 ? 0.9 : null;
                            if($isSingle) {
                                $registrationLose = Registration::where('id', $regFromPool['id'])->first();
                                $registrationLose->update(['position' => $newPositionForth]);
                                $clubsArray[] = $registrationLose->club_id;
                            }
                            if(!$isSingle) {
                                $registrationsToUpdate = Registration::where('team_id', $regFromPool['id'])->get();
                                foreach($registrationsToUpdate as $reg) {
                                    $reg->update(['position' => $newPositionForth]);
                                    $clubsArray[] = $reg->club_id;
                                }
                            }
                            
                        }
                    }
                }
            }
            if($goldPositions) {
                if($requestedPool->where('group', 3)->values()->count() != 0) {
                   
                    $resultsGOne = [];
                    $inputOne['points'] = $isSingle ? $requestedPool->where('group', 3)->first()->points_reg_one : $requestedPool->where('group', 3)->first()->points_team_one;
                    $inputOne['id'] = $isSingle ? $requestedPool->where('group', 3)->first()->registration_one : $requestedPool->where('group', 3)->first()->team_one;
                    $resultsGOne[] = $inputOne;
                    $inputTwo['points'] = $isSingle ? $requestedPool->where('group', 3)->first()->points_reg_two : $requestedPool->where('group', 3)->first()->points_team_two;
                    $inputTwo['id'] = $isSingle ? $requestedPool->where('group', 3)->first()->registration_two : $requestedPool->where('group', 3)->first()->team_two;
                    $resultsGOne[] = $inputTwo;
                    $unsortedCollection = collect($resultsGOne);
                    foreach($unsortedCollection->sortByDesc('points')->values() as $positon => $regFromPool){
                        $firstPosition = $regFromPool['points'] != 0 ? 3 : null;
                        $secondPosition = $regFromPool['points'] != 0 ? 2 : null;
                        if($positon == 0 && $regFromPool['id'] != null) {
                            if($isSingle) {
                                $regFirst = Registration::where('id', $regFromPool['id'])->first();
                                $regFirst->update(['position' => $firstPosition, 'status' => 1]);
                                $clubsArray[] = $regFirst->club_id;
                            }
                            if(!$isSingle) {
                                $registrationsToUpdate = Registration::where('team_id', $regFromPool['id'])->get();
                                foreach($registrationsToUpdate as $reg) {
                                    $reg->update(['position' => $firstPosition, 'status' => 1]);
                                    $clubsArray[] = $reg->club_id;
                                }
                            }
                            
                        }
                        if($positon == 1 && $regFromPool['id'] != null) {
                            if($isSingle) {
                                $regSecond = Registration::where('id', $regFromPool['id'])->first();
                                $regSecond->update(['position' => $secondPosition, 'status' => 1]);
                                $clubsArray[] = $regSecond->club_id;
                            }
                            if(!$isSingle) {
                                $registrationsToUpdate = Registration::where('team_id', $regFromPool['id'])->get();
                                foreach($registrationsToUpdate as $reg) {
                                    $reg->update(['position' => $secondPosition, 'status' => 1]);
                                    $clubsArray[] = $reg->club_id;
                                }
                            }
                            
                        }
                    }
                }
            }
        }
        $this->calculateResults($timeTable->compatition_id ,array_unique($clubsArray), 'results');
        return $this->success('', 'Uspjesno');
    }
}
