<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreImageRequest;
use App\Http\Resources\ClubsResource;
use App\Http\Resources\CompatitionsResource;
use App\Http\Resources\CompatitorsResource;
use App\Http\Resources\DocumentsResource;
use App\Http\Resources\PagesResource;
use App\Http\Resources\PostsResource;
use App\Http\Resources\SpecialPersonalsResource;
use App\Models\Club;
use App\Models\Compatition;
use App\Models\Compatitor;
use App\Models\Document;
use App\Models\Image;
use App\Models\Page;
use App\Models\Post;
use App\Models\SpecialPersonal;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{
    use HttpResponses;
    public function getFile($path)
    {
        $file = Storage::get($path);
        return response($file, 200)->header('Content-Type', Storage::url($path));
    }

    public function setCompatitorImage(StoreImageRequest $request, Compatitor $compatitor) {

        if(Auth::user()->user_type != 2 && Auth::user()->status == 0) {
            return $this->error('', 'Vaš nalog je suspendovan Kontatirajte KSCG!', 403);
        }
        if(Auth::user()->user_type == 0 && Auth::user()->club->id != $compatitor->club_id) {
            return $this->error('', 'Mozete da promijenite sliku samo članovima kluba!', 403);
        }
        $image = $compatitor->image()->get()->first();

        $path = Storage::putFile('compatitor-image', $request->image);

        if($image != null) {
            $url = $compatitor->image()->get()->first()->url;
            Storage::delete($url);
            $compatitor->image()->delete();
        }
        
        $compatitor->image()->create([
            'url' => $path
        ]);
        return new CompatitorsResource($compatitor);
    }

    public function setClubImage(StoreImageRequest $request, Club $club) {

        if(Auth::user()->user_type !== 2 && Auth::user()->status == 0) {
            return $this->error('', 'Vaš nalog je suspendovan Kontatirajte KSCG!', 403);
        }
        if(Auth::user()->user_type == 0 && Auth::user()->club->id != $club->id) {
            return $this->error('', 'Mozete da promijenite sliku samo svog kluba!', 403);
        }
        $image = $club->image()->get()->first();

        $path = Storage::putFile('club-image', $request->image);

        
        if($image != null) {
            $url = $club->image()->get()->first()->url;
            Storage::delete($url);
            $club->image()->delete();
        }

        $club->image()->create([
            'url' => $path
        ]);
        return new ClubsResource($club);
    }

    public function setSpecPersonImage(StoreImageRequest $request, SpecialPersonal $personal) 
    {
        if(Auth::user()->user_type != 2 && Auth::user()->status == 0) {
            return $this->error('', 'Vaš nalog je suspendovan Kontatirajte KSCG!', 403);
        }
        $image = $personal->image()->get()->first();

        $path = Storage::putFile('special-personal-image', $request->image);
        
        
        if($image != null) {
            $url = $personal->image()->get()->first()->url;
            Storage::delete($url);
            $personal->image()->delete();
        }

        $personal->image()->create([
            'url' => $path
        ]);
        return new SpecialPersonalsResource($personal);
    }

    public function addPostImage(StoreImageRequest $request, Post $post) 
    {
        $path = Storage::putFile('post-image', $request->image);
        
        $image = $post->images()->create([
            'url' => $path
        ]);

        if($request->coverImage == 'true') {
            $post->update([
                'cover_image' => $image->id
            ]);
        } 
        return new PostsResource($post);
    }
    public function addPageImage(StoreImageRequest $request, Page $page) 
    {
        $path = Storage::putFile('page-image', $request->image);
        
        $image = $page->images()->create([
            'url' => $path
        ]);

        if($request->coverImage == 'true') {
            $page->update([
                'cover_image' => $image->id
            ]);
        } 
        return new PagesResource($page);
    }
 
    public function deleteImage(Image $image) 
    {
        Storage::delete($image->url);
        $image->delete();
        return $this->success('', 'Slika je obrisana');
    }

    public function addDocumentCompatitor(Request $request, Compatitor $compatitor)
    {
        
        $path = Storage::putFile('compatitors-docs', $request->document);
        $compatitor->document()->create([
            'name' => $request->name,
            'doc_link' => $path
        ]);

        return new CompatitorsResource($compatitor);

    }

    public function addDocumentSpecialPersonal(Request $request, SpecialPersonal $special_personal)
    {
        
        $path = Storage::putFile('special-persons-docs', $request->document);
        $special_personal->document()->create([
            'name' => $request->name,
            'doc_link' => $path
        ]);

        return new SpecialPersonalsResource($special_personal);

    }
    public function addDocumentCompatition(Request $request, Compatition $compatition)
    {
        
        $path = Storage::putFile('compatition-docs', $request->document);
        $compatition->document()->create([
            'name' => $request->name,
            'doc_link' => $path
        ]);

        return new CompatitionsResource($compatition);

    }
    public function deleteDocument(Document $document)
    {

        $url = $document->where('id', '=', $document->id)->get()->first()->doc_link;
        Storage::delete($url);
        $document->where('id', $document->id)->delete();
        //return new SpecialPersonalsResource($special_personal);
        return response()->json($url);
    }
}
