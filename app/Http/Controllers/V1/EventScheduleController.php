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
    public function index()
    {
        $eventSchedule = EventSchedule::orderBy('id', 'desc');
        return EventScheduleResource::collection($eventSchedule->paginate(15));
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
    public function destroy(EventSchedule $eventSchedule)
    {
        $eventSchedule->delete();
        return $this->success('', 'Uspjesno obrisan event!');
    }
}
