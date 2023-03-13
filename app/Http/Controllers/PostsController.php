<?php

namespace App\Http\Controllers;

use App\Filters\PostsFilter;
use App\Http\Requests\StorePostsRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Http\Resources\PostsResource;
use App\Models\Image;
use App\Models\Post;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PostsController extends Controller
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
        $filter = new PostsFilter();
        $queryItems = $filter->transform($request); //[['column', 'operator', 'value']]
        $sort = $request->sort == null ? 'id' : $request->sort;
        $sortDirection = $request->sortDirection == null ? 'asc' : $request->sortDirection;
        $news = Post::orderBy($sort, $sortDirection);
        $per_page = $request->perPage;


        return PostsResource::collection($news->where($queryItems)->paginate($per_page));
    }

    public function index(Request $request)
    {
        $per_page = $request->perPage;
        return PostsResource::collection(Post::paginate($per_page));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StorePostsRequest $request)
    {
        $request->validated($request->all());

        $news = Post::create([
            'title' => $request->title,
            'content' => $request->content,
            'excerpt' => $request->excerpt,
            'user_id' => Auth::user()->id
        ]);
        $path = Storage::putFile('post-image', $request->image);
        $image = $news->images()->create([
            'url' => $path
        ]);
        $news->update([
            'cover_image' => $image->id
        ]);

        return new PostsResource($news);
       
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Post $news)
    {
        return new PostsResource($news);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdatePostRequest $request, Post $news)
    {
        $request->validated($request->all());
        $news->update($request->all());
        $news->update([
            'updated_at'=> date('Y:m:d H:i:s')
        ]);
        return new PostsResource($news);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Post $news)
    {
        foreach($news->images()->get() as $image) {
            Storage::delete($image->url);
        }
        
        $news->images()->delete();
        $news->document()->delete();
        $news->delete();
        
       return $this->success('','Uspjesno je obrisan post!');
    }
}
