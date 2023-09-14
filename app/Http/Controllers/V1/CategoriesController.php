<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Filters\CategoriesFilter;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Http\Resources\CategoriesForTimeTableResource;
use App\Http\Resources\CategoriesResource;
use App\Models\Category;
use App\Models\Compatition;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
        $sortDirection = $request->sortDirection == null ? 'desc' : $request->sortDirection;
        $paginate = $request->perPage;
        $category = Category::orderBy($sort, $sortDirection);
        $search = '%'. $request->search . '%';
        
        if($request->has('belts')) {
            $categoryWithBeltId = [];
            $getCategory = $category->get();
            foreach($getCategory as $cat) {
                $doesHaveBelt = false;
                foreach($cat->belts as $belt) {
                    if($belt->id == $request->belts){
                        $doesHaveBelt = true;
                    }
                }
                if($doesHaveBelt == true) {
                    $categoryWithBeltId[] = $cat->id;
                }
            }
            if($categoryWithBeltId != []) {
                $category = Category::orderBy($sort, $sortDirection)->whereIn('id', $categoryWithBeltId);
            }
        }

        return CategoriesResource::collection($category->where($queryItems)->where(DB::raw('CONCAT_WS(" ", name, category_name)'), 'like', $search)->paginate($paginate));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreCategoryRequest $request)
    {
        if(Auth::user()->user_type != 2) {
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
            'solo_or_team' => $request->soloOrTeam, //0=solo 1=team
            'match_lenght' => $request->matchLenght,      
            'repesaz' => $request->rematch,      
            'years_from' => $request->yearsFrom,  
            'years_to' => $request->yearsTo,  
            'status' => $request->status,
            'is_official' => $request->isOfficial        
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
        if(Auth::user()->user_type != 2) {
            return $this->restricted('', 'Not alowed!', 403);
        }
        $request->validated($request->all());
        
        $category->update($request->all());

        $request->has('categoryName') ? $category->update(['category_name' => $request->categoryName]) : null;
        $request->has('kataOrKumite') ? $category->update(['kata_or_kumite' => $request->kataOrKumite]) : null;
        $request->has('dateFrom') ? $category->update(['date_from' => $request->dateFrom])  : null;
        $request->has('dateTo') ? $category->update(['date_to' => $request->dateTo])  : null;
        $request->has('soloOrTeam') ? $category->update(['solo_or_team' => $request->soloOrTeam])  : null;
        $request->has('matchLenght') ? $category->update(['match_lenght' => $request->matchLenght])  : null;
        $request->has('yearsFrom') ? $category->update(['years_from' => $request->yearsFrom == '0' ? NULL : $request->yearsFrom])  : null;
        $request->has('yearsTo') ? $category->update(['years_to' => $request->yearsTo == '0' ? NULL : $request->yearsTo ])  : null;
        $request->has('rematch') ? $category->update(['repesaz' => $request->rematch])  : null;
        $request->has('isOfficial') ? $category->update(['is_official' => $request->isOfficial])  : null;
  

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
    
        if($category->compatitions->count() > 0) {
            return $this->error('', 'Deaktivirajte kategorije koje više ne koristite!', 403);
        }
        $category->delete();
        return $this->success('', 'Kategorija je uspjesno obrisana!');
    }

    public function catForTimeTable(Compatition $competition,Request $request)
    {
        return CategoriesForTimeTableResource::collection($competition->categories->sortBy('solo_or_team')->sortByDesc('kata_or_kumite'));
    }
}
