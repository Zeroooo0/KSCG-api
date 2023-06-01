<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Filters\TimeTableFilter;
use App\Http\Requests\StoreTimeTableMassRequest;
use App\Http\Requests\UpdateTimeTableRequest;
use App\Http\Resources\TimeTableResource;
use App\Models\Compatition;
use App\Models\TimeTable;
use App\Traits\HttpResponses;
use App\Traits\LenghtOfCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TimeTablesController extends Controller
{
    use HttpResponses;
    use LenghtOfCategory;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, Compatition $competition)
    {

        $per_page = $request->perPage; 
        $filter = new TimeTableFilter();
        $queryItems = $filter->transform($request); //[['column', 'operator', 'value']]
        $sort = $request->sort == null ? 'order_no' : $request->sort;
        $sortDirection = $request->sortDirection == null ? 'asc' : $request->sortDirection;
        $timeTable = TimeTable::orderBy('order_no', 'asc')->orderBy('tatami_no', 'asc')->where('compatition_id', $competition->id);
        if($request->has('perPage') && $request->perPage == 0) {
            return TimeTableResource::collection($timeTable->where($queryItems)->all());
        }
        return TimeTableResource::collection($timeTable->where($queryItems)->paginate($per_page));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $compatition = Compatition::where('id', $request->competitionId)->first();
        $start_time = $compatition->start_time_date;
        $category = $compatition->categories;

        $incomin_data = $request->tatamies;
        $timeTableData = $compatition->timeTable;
 
        $timeTable = [];

        foreach($incomin_data as $data) {
            $finishedTimeStore = $start_time;
            $numberOfCategory = count($data['categories']);
            
            for($j = 0; $j <= $numberOfCategory - 1; $j++) {
                $categoryNow = $category->where('id', $data['categories'][$j])->first();
                
                $registrationsData = $this->categoryDuration($compatition , $categoryNow);
                $registrations = $registrationsData['categoryRegistrations'];
                $totalTimePerCat = $registrationsData['categoryDuration'] * 60;
                
                $input['compatition_id'] = $request->competitionId;
                $input['category_id'] = $categoryNow->id;
                $input['tatami_no'] = $data['tatami'];
                $input['order_no'] = $j + 1;
                $input['status'] = 0;
                $input['pairs'] = $registrations;
                $input['eto_start'] = $finishedTimeStore;
                $finishedTimeStore = Date("Y-m-d H:i:s", strtotime("$totalTimePerCat seconds", strtotime($finishedTimeStore)));
                $input['eto_finish'] = $finishedTimeStore;

                $timeTable[] = $input;
            }
        }



        if($timeTableData->count() >= 1) {
            // if($timeTableData->where('status', '!=', 0)->count() > 0) {
            //     return $this->error('', 'Takmičenje je već započelo!', 403);
            // }
            TimeTable::destroy($timeTableData);
        }
        TimeTable::insert($timeTable);
        return $this->success($timeTable);
    }
    public function show(TimeTable $time_table) 
    {
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
