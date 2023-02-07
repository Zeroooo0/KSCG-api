<?php

namespace App\Http\Controllers;

use App\Http\Resources\ClubsResource;
use App\Http\Resources\CompatitorsResource;
use App\Http\Resources\SpecialPersonalsResource;
use App\Models\Club;
use App\Models\Compatitor;
use App\Models\SpecialPersonal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FileController extends Controller
{
    public function getImage($path)
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
}
