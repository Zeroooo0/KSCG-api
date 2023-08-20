<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEventScheduleRequest;
use App\Http\Resources\EventScheduleResource;
use App\Models\Compatitor;
use App\Models\EventSchedule;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;

class EventScheduleController extends Controller
{
    use HttpResponses;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $year = $request->year;
        
        if($request->has('year')) {
            $dateFrom = date("$year-1-1");
            $dateTo = date("$year-12-31");
            $eventSchedule = EventSchedule::orderBy('start', 'desc')->where('start', ">=", $dateFrom)->where('start', "<=", $dateTo);
            return EventScheduleResource::collection($eventSchedule->get());
        }
        $eventSchedule = EventSchedule::orderBy('id', 'desc');
        return EventScheduleResource::collection($eventSchedule->paginate($request->perPage));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreEventScheduleRequest $request)
    {
        $request->validated($request->all());
        $eventSchedule = EventSchedule::create([
            'name' => $request->name,
            'start' => $request->start,
            'end' => $request->end,
            'is_all_day' => $request->isAllDay,
            'bg_color' => $request->bgColor,
        ]);
        return $this->success(new EventScheduleResource($eventSchedule), 'Uspjesno kreiran Event.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(EventSchedule $event)
    {
        
        $event->delete();
        return $this->success('', 'Uspjesno obrisan event!');
    }
}
