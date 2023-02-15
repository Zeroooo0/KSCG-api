<?php

namespace App\Http\Controllers;

use App\Http\Resources\CategoriesResource;
use App\Http\Resources\CompatitionsResource;
use App\Models\Category;
use App\Models\Compatition;
use Illuminate\Http\Request;

class CategoriesInCompatitionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request) 
    {
        $compatition = Compatition::find($request->compatitionId);
        
        $arr = [];
        foreach ($compatition->categories as $compatition) {
            $categoryList = $compatition->pivot->category_id; 
            $arr[] = (string)$categoryList;
        }
        //return response($compatition->categories->where('id', 1));
        $notSelected = Category::whereNotIn('id', $arr)->where('status', 1)->get();
        $selected = Category::whereIn('id', $arr)->where('status', 1)->get();
        return  CategoriesResource::collection($selected, $notSelected);
    }
}
