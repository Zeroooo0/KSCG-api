<?php

namespace App\Http\Controllers;

use App\Filters\CategoriesFilter;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
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
        $filter = new CategoriesFilter();
        $queryItems = $filter->transform($request);
        $sort = $request->sort == null ? 'id' : $request->sort;
        $sortDirection = $request->sortDirection == null ? 'asc' : $request->sortDirection;
        $paginate = $request->perPage;
        $category = Category::orderBy($sort, $sortDirection);
        return CategoriesResource::collection($category->where($queryItems)->paginate($paginate));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreCategoryRequest $request)
    {
        if(Auth::user()->user_type !== 2) {
            return $this->restricted('', 'Not alowed!', 403);
        }
        $request->validated($request->all());
        
        $category = Category::create([
            'name' => $request->name,
            'kata_or_kumite' => $request->kataOrKumite,
            'category_name' => $request->categoryName,
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
    public function update(UpdateCategoryRequest $request, Category $category)
    {
        if(Auth::user()->user_type !== 2) {
            return $this->restricted('', 'Not alowed!', 403);
        }
        $request->validated($request->all());
        
        $category->update($request->except(['kateOrKumite', 'dateFrom', 'dateTo', 'weightFrom', 'weightTo']));
        $request->has('kataOrKumite') ? $category->update(['kata_or_kumite' => $request->kataOrKumite]) : null;
        $request->has('dateFrom') ? $category->update(['date_from' => $request->dateFrom])  : null;
        $request->has('dateTo') ? $category->update(['date_to' => $request->dateTo])  : null;
        $request->has('weightFrom') ? $category->update(['weight_from' => $request->weightFrom])  : null;
        $request->has('weightTo') ? $category->update(['weight_to' => $request->weightTo])  : null;
        $request->has('soloOrTeam') ? $category->update(['solo_or_team' => $request->soloOrTeam])  : null;
        $request->has('matchLenght') ? $category->update(['match_lenght' => $request->matchLenght])  : null;

        $belts = array_filter(explode(',', $request->belts));

        $request->has('belts') ? $category->belts()->sync($belts)  : null;
        
        

        return new CategoriesResource($category);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Category $category)
    {
        if($category->compatitions !== null ? $category->compatitions->count() : 0 > 0) {
            return $this->error('', 'Deaktivirajte kategorije koje više ne koristite!', 403);
        }
        $category->delete();
        return $this->success('', 'Kategorija je uspjesno obrisana!');


    }
}
