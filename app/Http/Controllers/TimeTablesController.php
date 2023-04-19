<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTimeTableMassRequest;
use App\Http\Requests\UpdateTimeTableRequest;
use App\Http\Resources\TimeTableResource;
use App\Models\Compatition;
use App\Models\TimeTable;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TimeTablesController extends Controller
{
    use HttpResponses;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, Compatition $competition)
    {

        //$filter = new SpecialPersonalsFilter();
        //$queryItems = $filter->transform($request); //[['column', 'operator', 'value']]
        $per_page = $request->perPage;
        $timeTable = TimeTable::orderBy('tatami_no', 'asc')->orderBy('order_no', 'asc')->where('compatition_id', $competition->id);
        


        return TimeTableResource::collection($timeTable->paginate($per_page));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreTimeTableMassRequest $request)
    {
        $compatition = Compatition::where('id', collect($request->all())->values()->first()['competitionId'])->first();
        $number_of_tatami = $compatition->tatami_no;
        $start_time = $compatition->start_time_date;
        $pairs = $compatition->registrations;
        $category = $compatition->categories;
        $reg_singles = $pairs->where('team_or_single', 1);
        $reg_team = $pairs->where('team_or_single', 0);
        $incomin_data = collect($request->all())->except('competitionId')->values()->sortBy('orderNo');
        $timeTableData = $compatition->timeTable;

        if($incomin_data->where('tatamiNo', '>', $number_of_tatami)->count() > 0 || $incomin_data->where('tatamiNo', '<=', 0)->count() > 0) {
            return $this->error('', "Tatami pocinje sa indexom 1 i može maksimalno da sadrži $number_of_tatami!", 404);
        }
        
        
        $timeTable = [];
        for($i = 1; $i <= $number_of_tatami; $i++) {
            $tatmiIncomingData = $incomin_data->where('tatamiNo', $i);
            $orderNoByTatami = $tatmiIncomingData->countBy('orderNo');

            foreach($orderNoByTatami as $orNo=>$count) {
                if($count > 1) {
                    return $this->error('', 'Redni broj kategorije na borilištu se ne moze ponavljati!', 403);
                }
            }
            $finishedTimeStore = $start_time;
            foreach($tatmiIncomingData as $data) {
                
                $teamOrSingle = $pairs->where('category_id', $data['categoryId'])->first()->team_or_single;

                $teamCount =  $reg_team->where('category_id', $data['categoryId'])->groupBy('team_id')->count();
                $singleCount = $reg_singles->where('category_id', $data['categoryId'])->count();
                $registrations = $teamOrSingle == 0 ? $teamCount : $singleCount;
      
                $timePerCategory = $category->where('id', $data['categoryId'])->first()->match_lenght ?? 0;
                $repesaz = $category->where('id', $data['categoryId'])->first()->repesaz == true ? 2 * $timePerCategory : 0;
                $totalTimePerCat = 0;
                
                switch($registrations) {
                    case $registrations == 0:
                        break;
                    case $registrations <= 2:
                        $totalTimePerCat = ($registrations - ( 2 - $registrations)) / 2 * $timePerCategory;
                        break;
                    case $registrations <= 4:
                        $totalTimePerCat = ($registrations - ( 4 - $registrations)) / 2 * $timePerCategory + $timePerCategory + $repesaz;
                        break;
                    case $registrations <= 8:
                        $totalTimePerCat = ($registrations - ( 8 - $registrations)) / 2 * $timePerCategory + 2 * $timePerCategory + $timePerCategory + 2 * $repesaz;
                        break;
                    case $registrations <= 16:
                        $totalTimePerCat = ($registrations - ( 16 - $registrations)) / 2 * $timePerCategory + 4 * $timePerCategory + 2 * $timePerCategory + $timePerCategory + 3 * $repesaz;
                        break;
                    case $registrations <= 32:
                        $totalTimePerCat = ($registrations - ( 32 - $registrations)) / 2 * $timePerCategory + 8 * $timePerCategory + 4 * $timePerCategory + 2 * $timePerCategory + $timePerCategory + 4 * $repesaz;
                        break;
                    case $registrations <= 64:
                        $totalTimePerCat = ($registrations - ( 64 - $registrations)) / 2 * $timePerCategory + 16 * $timePerCategory + 8 * $timePerCategory + 4 * $timePerCategory + 2 * $timePerCategory + $timePerCategory + 5 * $repesaz;
                        break;
                        
                }
            

                $input['compatition_id'] = collect($request->all())->values()->first()['competitionId'];
                $input['category_id'] = $data['categoryId'];
                $input['tatami_no'] = $data['tatamiNo'];
                $input['order_no'] = $data['orderNo'];
                $input['status'] = 0;
                $input['pairs'] = $registrations;
                $input['eto_start'] = $finishedTimeStore;
                $finishedTimeStore = Date("Y-m-d H:i:s", strtotime("$totalTimePerCat minutes", strtotime($finishedTimeStore)));
                $input['eto_finish'] = $finishedTimeStore;

                $timeTable[] = $input;
            }
        }

        if($timeTableData->count() >= 1) {
            if($timeTableData->where('status', '!=', 0)->count() > 0) {
                return $this->error('', 'Takmičenje je već započelo!', 403);
            }
            TimeTable::destroy($timeTableData);
        }
        TimeTable::insert($timeTable);
        return $this->success($timeTable);
    }
    public function show(TimeTable $time_table) 
    {
        return $time_table;
        return new TimeTableResource($time_table);
    }

    public function updateTime(Request $request, TimeTable $time_table) 
    {
        if($request->has('status')) {

            if($request->status == 1 && $time_table->status < 1) {
                $time_table->update([
                    'status' => $request->status,
                    'started_time' => date('H:i:s')
                ]);
            }
            if($request->status == 2 && $time_table->status < 2) {
                $time_table->update([
                    'status' => $request->status,
                    'finish_time' => date('H:i:s')
                ]);
            }
        }

        return  $this->success($time_table, 'Uspješno ispravljen Time Table.');
        
    }
}
