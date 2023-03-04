<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePostsRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Http\Resources\PostsResource;
use App\Models\Image;
use App\Models\Post;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        return PostsResource::collection(Post::paginate($per_page));
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

        $post = Post::create([
            'title' => $request->title,
            'content' => $request->content,
            'excerpt' => $request->excerpt,
            'user_id' => Auth::user()->id
        ]);
        $path = Storage::putFile('post-image', $request->image);
        $image = $post->images()->create([
            'url' => $path
        ]);
        $post->update([
            'cover_image' => $image->id
        ]);

        return new PostsResource($post);
       
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Post $post)
    {
        return new PostsResource($post);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdatePostRequest $request, Post $post)
    {
        $request->validated($request->all());
        $post->update($request->all());
        $post->update([
            'updated_at'=> date('Y:m:d H:i:s')
        ]);
        return new PostsResource($post);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Post $post)
    {
        foreach($post->images()->get() as $image) {
            Storage::delete($image->url);
        }
        
        $post->images()->delete();
        $post->document()->delete();
        $post->delete();
        
       return $this->success('','Uspjesno je obrisan post!');
    }
}
