<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePagesRequest;
use App\Http\Requests\UpdatePageRequest;
use App\Http\Resources\PagesResource;
use App\Models\Page;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PagesController extends Controller
{
    use HttpResponses;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function public(Request $request)
    {
        $per_page = $request->perPage;
        $sort = $request->sort == null ? 'id' : $request->sort;
        $sortDirection = $request->sortDirection == null ? 'desc' : $request->sortDirection;

        return PagesResource::collection(Page::orderBy($sort, $sortDirection)->paginate($per_page));
    }

    public function index(Request $request)
    {
        $per_page = $request->perPage;
        $sort = $request->sort == null ? 'id' : $request->sort;
        $sortDirection = $request->sortDirection == null ? 'desc' : $request->sortDirection;

        return PagesResource::collection(Page::orderBy($sort, $sortDirection)->paginate($per_page));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StorePagesRequest $request)
    {
        $request->validated($request->all());

        $page = Page::create([
            'slug' => str_replace(' ', '-', strtolower(trim($request->title, '.'))),
            'title' => $request->title,
            'content' => $request->content,
            'excerpt' => $request->excerpt,
            'user_id' => Auth::user()->id
        ]);
        $path = Storage::putFile('post-image', $request->image);
        $image = $page->images()->create([
            'url' => $path
        ]);
        $page->update([
            'cover_image' => $image->id
        ]);

        return new PagesResource($page);
       
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Page $page)
    {
        return new PagesResource($page);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdatePageRequest $request, Page $page)
    {
        $request->validated($request->all());
        $page->update($request->all());
        $page->update([
            'updated_at'=> date('Y:m:d H:i:s')
        ]);
        return new PagesResource($page);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Page $page)
    {

        $page->delete();
        $page->images()->delete();
        $page->document()->delete();
        
        return $this->success('','Uspjesno je obrisan stranica!');
    }
}