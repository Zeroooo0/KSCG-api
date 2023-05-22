<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreComponentRequest;
use App\Http\Requests\UpdateComponentRequest;
use App\Http\Resources\ComponentResource;
use App\Models\Component;
use App\Models\Page;
use App\Models\Post;
use App\Models\SpecialPersonal;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ComponentController extends Controller
{

    use HttpResponses;
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storePageComponent(StoreComponentRequest $request, Page $page)
    {
        $request->validated($request->all());
        $component = $page->components()->create([
            'type' => $request->type,
            'title' => $request->title,
            'order_number' => $request->has('orderNumber') ? $request->orderNumber : null,
        ]);
        return new ComponentResource($component);
        
    }


    public function storePostComponent(StoreComponentRequest $request, Post $news)
    {
        $request->validated($request->all());
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
    public function update(UpdateComponentRequest $request, Component $component)
    {
        $component->update($request->except('orderNumber'));
        !$request->has('orderNumber') ? null : $component->update(['order_number' => $request->orderNumber]);
        return new ComponentResource($component);
    }
    public function show(Request $request, Component $component)
    {
        return new ComponentResource($component);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Component $component)
    {
        $component->documents()->delete();
        $component->images()->delete();
        $component->roles()->delete();
        $component->delete();
        return $this->success('', 'Uspješno obrisana komponenta.');
    }
}
