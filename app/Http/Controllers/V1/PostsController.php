<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Filters\PostsFilter;
use App\Http\Requests\StorePostsRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Http\Resources\ComponentResource;
use App\Http\Resources\PostsResource;
use App\Models\Post;
use App\Support\Collection;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use ImagesResize;

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
        $sortDirection = $request->sortDirection == null ? 'desc' : $request->sortDirection;
        $news = Post::orderBy($sort, $sortDirection);
        $per_page = $request->perPage;


        return PostsResource::collection($news->where($queryItems)->paginate($per_page));
    }

    public function index(Request $request)
    {
        $per_page = $request->perPage; 
        $filter = new PostsFilter();
        $queryItems = $filter->transform($request); //[['column', 'operator', 'value']]
        $sort = $request->sort == null ? 'id' : $request->sort;
        $sortDirection = $request->sortDirection == null ? 'desc' : $request->sortDirection;
        $news = Post::orderBy($sort, $sortDirection);

        return PostsResource::collection($news->where($queryItems)->paginate($per_page));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StorePostsRequest $request)
    {
        if(Auth::user()->user_type == 0 || Auth::user()->status == 0) {
            return $this->error('','Ova funkcionalnost vam nije dozvoljena!', 403); 
        }
        $request->validated($request->except('slug'));

        $news = Post::create([
            'slug' => str_replace(' ', '-', strtolower(trim($request->title, '.'))),
            'title' => $request->title,
            'content' => $request->content,
            'gallery' => $request->gallery == null ? false : $request->gallery,
            'excerpt' => $request->excerpt,
            'user_id' => Auth::user()->id
        ]);

        $news->update(['slug' => $news->slug . '-' . $news->id]);

        if($request->has('image')){
            $tempImage = $request->image;
            $image_name = time().'_'.$tempImage->getClientOriginalName();
            $storePath = storage_path('app/post-image/') . $image_name;
            $path = 'post-image/' . $image_name;
            ImagesResize::make($tempImage->getRealPath())->resize(1920, 1080)->save($storePath);
            
            $image = $news->images()->create([
                'url' => $path
            ]);
            $news->update([
                'cover_image' => $image->id
            ]);
        }


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
    public function showPublic(Request $request, Post $news)
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
        if(Auth::user()->user_type == 0 || Auth::user()->status == 0) {
            return $this->error('','Ova funkcionalnost vam nije dozvoljena!', 403); 
        }
        $request->validated($request->all());
        $news->update($request->except('image'));
        if($request->has('image')){
            $tempImage = $request->image;
            $image_name = time().'_'.$tempImage->getClientOriginalName();
            $storePath = storage_path('app/post-image/') . $image_name;
            $path = 'post-image/' . $image_name;
            ImagesResize::make($tempImage->getRealPath())->resize(1920, 1080)->save($storePath);
            $image = $news->images()->create([
                'url' => $path
            ]);
            if($request->has('coverImage') && $request->coverImage == (true || 'true') ){
                $news->update([
                    'cover_image' => $image->id
                ]);
            }

        }
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
        if(Auth::user()->user_type == 0 || Auth::user()->status == 0) {
            return $this->error('','Ova funkcionalnost vam nije dozvoljena!', 403); 
        }
        foreach($news->images()->get() as $image) {
            Storage::delete($image->url);
        }
        
        $news->images()->delete();
        $news->document()->delete();
        $news->delete();
        
       return $this->success('','Uspjesno je obrisan post!');
    }

    public function postComponents(Request $request, Post $news)
    {
        return ComponentResource::collection((new Collection($news->components))->paginate($request->perPage));
    }
}
