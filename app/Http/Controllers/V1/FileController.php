<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDocumentRequest;
use App\Http\Requests\StoreImageRequest;
use App\Http\Resources\ClubsResource;
use App\Http\Resources\CompatitionsResource;
use App\Http\Resources\CompatitorsResource;
use App\Http\Resources\DocumentsResource;
use App\Http\Resources\ImageResource;
use App\Http\Resources\PagesResource;
use App\Http\Resources\PostsResource;
use App\Http\Resources\RolesResource;
use App\Http\Resources\SpecialPersonalsResource;
use App\Models\Club;
use App\Models\Compatition;
use App\Models\Compatitor;
use App\Models\Component;
use App\Models\Document;
use App\Models\Image;
use App\Models\Page;
use App\Models\Post;
use App\Models\SpecialPersonal;
use App\Traits\HttpResponses;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;

class FileController extends Controller
{
    use HttpResponses;
    public function getFile($path)
    {
        $file = Storage::get($path);
        return response($file, 200)->header('Content-Type', Storage::url($path));
    }

    public function setCompatitorImage(StoreImageRequest $request, Compatitor $compatitor) {
        //return $request->safe()->except('coverImage');
        $request->safe()->except('coverImage');
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
        $request->safe()->except('coverImage');
        if(Auth::user()->user_type != 2 && Auth::user()->status == 0) {
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
    public function setCompatitionImage(StoreImageRequest $request, Compatition $compatition) 
    {
        $request->safe()->except('coverImage');
        if(Auth::user()->user_type != 2 && Auth::user()->status == 0) {
            return $this->error('', 'Vaš nalog je suspendovan Kontatirajte KSCG!', 403);
        }
        $image = $compatition->image()->get()->first();

        $path = Storage::putFile('club-image', $request->image);

        
        if($image != null) {
            $url = $compatition->image()->get()->first()->url;
            Storage::delete($url);
            $compatition->image()->delete();
        }

        $compatition->image()->create([
            'url' => $path
        ]);
        return new CompatitionsResource($compatition);
    }

    public function setSpecPersonImage(StoreImageRequest $request, SpecialPersonal $personal) 
    {
        $request->safe()->except('coverImage');
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

    public function addPostImage(StoreImageRequest $request, Post $news) 
    {
        
        $request->safe()->except('coverImage');
       
        $path = Storage::putFile('post-image', $request->image);
        
        $image = $news->images()->create([
            'url' => $path
        ]);
        
        if($request->has('coverImage') && $request->coverImage == 'true' || $request->coverImage == 1) {
            $news->update([
                'cover_image' => $image->id
            ]);
        } 
        return new PostsResource($news);
    }
    public function addPageImage(StoreImageRequest $request, Page $page) 
    {
        $request->validated($request->all());
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

    public function addDocumentCompatitor(StoreDocumentRequest $request, Compatitor $compatitor)
    {
        $request->validated($request->all());
        $path = Storage::putFile('compatitors-docs', $request->document);
        $compatitor->document()->create([
            'name' => $request->name,
            'doc_link' => $path
        ]);

        return new CompatitorsResource($compatitor);

    }
    public function addDocumentPage(StoreDocumentRequest $request, Page $page)
    {
        $request->validated($request->all());
        $path = Storage::putFile('page-docs', $request->document);
        $page->document()->create([
            'name' => $request->name,
            'doc_link' => $path
        ]);

        return new PagesResource($page);

    }
    public function addDocumentPost(StoreDocumentRequest $request, Post $news)
    {
        $request->validated($request->all());
        $path = Storage::putFile('page-docs', $request->document);
        $news->document()->create([
            'name' => $request->name,
            'doc_link' => $path
        ]);

        return new PostsResource($news);

    }

    public function addDocumentSpecialPersonal(StoreDocumentRequest $request, SpecialPersonal $special_personal)
    {
        $request->validated($request->all());
        $path = Storage::putFile('special-persons-docs', $request->document);
        $special_personal->document()->create([
            'name' => $request->name,
            'doc_link' => $path
        ]);

        return new SpecialPersonalsResource($special_personal);

    }
    public function addDocumentCompatition(StoreDocumentRequest $request, Compatition $compatition)
    {
        $request->validated($request->all());
        $path = Storage::putFile('compatition-docs', $request->document);
        $compatition->document()->create([
            'name' => $request->name,
            'doc_link' => $path
        ]);

        return new CompatitionsResource($compatition);

    }
    public function deleteDocument(Document $document)
    {
        $type = $document->documentable_type;
        $ownerId = $document->documentable_id;
        if(Auth::user()->user_type != 2 && Auth::user()->status == 0) {
            return $this->error('','Trentno vam je nalog suspendovan kontaktirajte KSCG!',403);
        }
        if(Auth::user()->user_type == 0) {
            if(str_contains($type, 'SpecialPersonal') || str_contains($type, 'Compatition')){
                return $this->error('', 'Klub administrator moze obrisati samo dokumenta koja posjeduju njegovi takmicari!', 403);
            }
            if(str_contains($type, 'Compatitor') || str_contains($type, 'Compatition')){
                $compatitor = Compatitor::where('id', $ownerId)->first()->club_id; 
                if($compatitor != Auth::user()->club->id){
                    return $this->error('', 'Ovaj dokument ne pripada vasem takmicaru!', 403);
                }
            }
        }
        $url = $document->where('id', '=', $document->id)->get()->first()->doc_link;
        Storage::delete($url);
        $document->where('id', $document->id)->delete();
        return $this->success('', 'Uspjesno obrisan dokument');
    }
    public function compatitorDocuments(Compatitor $compatitor, Request $request)
    {
        $per_page = $request->perPage;
        if(Auth::user()->user_type == 0 && $compatitor->club_id != Auth::user()->club->id) {
            return $this->error('', 'Nije vam dozvoljeno da brisete dokumenta takmicara koji nisu u vasem klubu!', 403);
        }
        return DocumentsResource::collection($compatitor->document()->paginate($per_page));
    }

    public function specialPersonalDocuments(Request $request, SpecialPersonal $specialPersonal)
    {
        $per_page = $request->perPage;

        return DocumentsResource::collection($specialPersonal->document()->paginate($per_page));
    }

    public function compatitionDocuments(Request $request, Compatition $compatition)
    {
        //
    }

    //Component data managament
    public function storeComponentDocs(Request $request, Component $component)
    {
        $path = Storage::putFile('component-docs', $request->document);
        $document = $component->documents()->create([
            'name' => $request->name,
            'doc_link' => $path
        ]);
        return $this->success(new DocumentsResource($document), 'Uspjesno dodat dokument');
      
    }

    public function storeComponentImage(Request $request, Component $component)
    {
        $path = Storage::putFile('component-image', $request->image);
        $image = $component->images()->create([
            'url' => $path
        ]);            
        return $this->success(new ImageResource($image), 'Uspjesno dodata slika');
    }


}
