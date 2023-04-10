<?php

namespace App\Http\Controllers;

use App\Http\Resources\ComponentResource;
use App\Models\Component;
use App\Models\Page;
use App\Models\Post;
use App\Models\SpecialPersonal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ComponentController extends Controller
{


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storePageComponent(Request $request, Page $page)
    {
        $component = $page->components()->create([
            'type' => $request->type,
            'title' => $request->title,
            'order_number' => $request->has('orderNumber') ? $request->orderNumber : null,
        ]);
        return new ComponentResource($component);
        
    }


    public function storePostComponent(Request $request, Post $news)
    {
        $component = $news->components()->create([
            'type' => $request->type,
            'title' => $request->title,
            'order_number' => $request->has('orderNumber') ? $request->orderNumber : null,
        ]);
        return new ComponentResource($component); 
    }
    
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Component $componenet)
    {
        //
    }
}
