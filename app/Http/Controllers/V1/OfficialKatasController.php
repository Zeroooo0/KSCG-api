<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\OfficialKataResource;
use App\Models\OfficialKata;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;

class OfficialKatasController extends Controller
{
    use HttpResponses;
    public function index(Request $request)
    {
        return OfficialKataResource::collection(OfficialKata::all());
    }
    // public function store(Request $request)
    // {
    //     $data = [];
    //     foreach($request->all() as $kata) {
    //         $input['name'] = $kata['name'];
    //         $input['created_at'] = now();
    //         $input['updated_at'] = now();
    //         $data[] = $input;
    //     }
    //     OfficialKata::insert($data );
    //     return $this->success('', 'Done!');
    // }
}
