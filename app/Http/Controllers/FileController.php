<?php

namespace App\Http\Controllers;

use App\Http\Resources\ClubsResource;
use App\Http\Resources\CompatitorsResource;
use App\Http\Resources\DocumentsResource;
use App\Http\Resources\SpecialPersonalsResource;
use App\Models\Club;
use App\Models\Compatitor;
use App\Models\SpecialPersonal;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{
    use HttpResponses;
    public function getFile($path)
    {
        $image = Storage::get($path);
        return response($image, 200)->header('Content-Type', Storage::url($path));
    }
    public function setCompatitorImage(Request $request, Compatitor $compatitor) {

        
        $image = $compatitor->image()->get()->first();

        $path = Storage::putFile('compatitor-image', $request->image);
        
        
        if($image !== null) {
            $url = $compatitor->image()->get()->first()->url;
            Storage::delete($url);
            $compatitor->image()->delete();
        }
        
        $compatitor->image()->create([
            'url' => $path
        ]);
        return new CompatitorsResource($compatitor);
    }

    public function setClubImage(Request $request, Club $club) {

        
        $image = $club->image()->get()->first();

        $path = Storage::putFile('club-image', $request->image);
        
        
        if($image !== null) {
            $url = $club->image()->get()->first()->url;
            Storage::delete($url);
            $club->image()->delete();
        }

        $club->image()->create([
            'url' => $path
        ]);
        return new ClubsResource($club);
    }

    public function setSpecPersonImage(Request $request, SpecialPersonal $personal) {

        
        $image = $personal->image()->get()->first();

        $path = Storage::putFile('special-personal-image', $request->image);
        
        
        if($image !== null) {
            $url = $personal->image()->get()->first()->url;
            Storage::delete($url);
            $personal->image()->delete();
        }

        $personal->image()->create([
            'url' => $path
        ]);
        return new SpecialPersonalsResource($personal);
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
    public function deleteDocumentCompatitor(Request $request, Compatitor $compatitor)
    {
        $url = $compatitor->document()->where('id', '=', $request->documentId)->get()->first()->doc_link;
        Storage::delete($url);
        $compatitor->document()->where('id', $request->documentId)->delete();

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
    public function deleteDocumentSpecialPersonal(Request $request, SpecialPersonal $special_personal)
    {
        $url = $special_personal->document()->where('id', '=', $request->documentId)->get()->first()->doc_link;
        Storage::delete($url);
        $special_personal->document()->where('id', $request->documentId)->delete();

        //return new SpecialPersonalsResource($special_personal);
        return response()->json($url);
    }




}
