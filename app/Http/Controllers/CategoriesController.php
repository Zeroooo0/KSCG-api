<?php

namespace App\Http\Controllers;

use App\Http\Resources\CategoriesResource;
use App\Models\Category;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CategoriesController extends Controller
{
    use HttpResponses;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {   
        $sort = $request->sort == null ? 'id' : $request->sort;
        $sortDirection = $request->sortDirection == null ? 'desc' : $request->sortDirection;
        $paginate = $request->perPage;
        $category = Category::orderBy($sort, $sortDirection);
        return CategoriesResource::collection($category->paginate($paginate));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if(Auth::user()->user_type !== 2) {
            return $this->restricted('', 'Not alowed!', 403);
        }
        
        
        $category = Category::create([
            'name' => $request->name,
            'kata_or_kumite' => $request->kataOrKumite,
            'gender' => $request->gender, //1=Male 2=Femail 3=M+F
            'date_from' => $request->dateFrom,
            'date_to' => $request->dateTo,
            'weight_from' => $request->weightFrom,
            'weight_to' => $request->weightTo,
            'solo_or_team' => $request->soloOrTeam, //0=solo 1=team
            'match_lenght' => $request->matchLenght,      
            'status' => 1    
        ]);

        $belts = explode(',', $request->belts);
  
        $category->belts()->attach(array_filter($belts));
        return new CategoriesResource($category);

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Category $category)
    {

        return new CategoriesResource($category);
        
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Category $category)
    {
        if(Auth::user()->user_type !== 2) {
            return $this->restricted('', 'Not alowed!', 403);
        }
        $category->update($request->except(['kateOrKumite', 'dateFrom', 'dateTo', 'weightFrom', 'weightTo']));

        $category->update([
            $request->has('kateOrKumite') ?? 'kata_or_kumite' =>  $request->kateOrKumite, //0=Kata 1=Kumita
            $request->has('dateFrom') ?? 'date_from' => $request->dateFrom,
            $request->has('dateTo') ?? 'date_to' => $request->dateTo,
            $request->has('weightFrom') ?? 'weight_from' => $request->weightFrom,
            $request->has('weightTo') ?? 'weight_to' => $request->weightTo,
            $request->has('soloOrTeam') ?? 'solo_or_team' => $request->soloOrTeam, //0=solo 1=team
            $request->has('matchLenght') ?? 'match_lenght' => $request->matchLenght,       
        ]);

        return new CategoriesResource($category);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
