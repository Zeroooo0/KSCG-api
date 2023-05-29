<?php

use App\Http\Controllers\V1\AuthController;
use App\Http\Controllers\V1\CategoriesController;
use App\Http\Controllers\V1\ClubsController;
use App\Http\Controllers\V1\ComponentController;
use App\Http\Controllers\V1\CompatitorsController;
use App\Http\Controllers\V1\CompatitionsController;
use App\Http\Controllers\V1\FileController;
use App\Http\Controllers\V1\PagesController;
use App\Http\Controllers\V1\PoolsController;
use App\Http\Controllers\V1\PostsController;
use App\Http\Controllers\V1\RegistrationsController;
use App\Http\Controllers\V1\ReusableDataController;
use App\Http\Controllers\V1\SpecialPersonalsController;
use App\Http\Controllers\V1\TeamsController;
use App\Http\Controllers\V1\TimeTablesController;
use App\Http\Controllers\V1\UsersController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;



Route::get('file/{path}', [FileController::class, 'getFile'])->where('path', '.*');

Route::group(['prefix' => 'v1/public'], function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::get('/clubs', [ClubsController::class, 'public']);  
    Route::get('/clubs/{club}', [ClubsController::class, 'show_public']);  
    Route::get('/club-results/{club}', [ReusableDataController::class, 'clubsResults']); 
    Route::get('/club-competitors/{club}', [ReusableDataController::class, 'clubCompatitors']);
    Route::get('/competitors', [CompatitorsController::class, 'public']);
    Route::get('/competitors/{competitor}', [CompatitorsController::class, 'show_public']);
    Route::get('/competitions', [CompatitionsController::class, 'public']);
    Route::get('/competition-categories/{competition}', [CompatitionsController::class, 'piblicCategories']);
    Route::get('/competition-results/{competition}', [CompatitionsController::class, 'piblicRegistrations']);
    Route::get('/competition-clubs-results', [ReusableDataController::class, 'registeredClubs']);
    Route::get('/time-table/{competition}', [TimeTablesController::class, 'index']);
    Route::get('/time-table-one/{time_table}', [TimeTablesController::class, 'show']); 
    Route::get('/news', [PostsController::class, 'public']);
    Route::get('/news/{news}', [PostsController::class, 'showPublic']);
    Route::get('/pages', [PagesController::class, 'public']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPasswordNotification']);
});

// Verify email



Route::group(['prefix' => 'v1', 'middleware' => ['auth:sanctum', 'ability:admin,club,commission']], function () {    
    //All auth users
    Route::post('/logout', [AuthController::class, 'logout']); 
    Route::post('/change-password/{user}',[AuthController::class, 'changePassword']);
    //File managing
    //Images update
    Route::post('/competitor-image/{compatitor}', [FileController::class, 'setCompatitorImage']);
    Route::post('/club-image/{club}', [FileController::class, 'setClubImage']);
    Route::post('/special-personnel-image/{personal}', [FileController::class, 'setSpecPersonImage']);
    Route::post('/competition-image/{compatition}', [FileController::class, 'setCompatitionImage']);
    //Image add
    Route::post('/add-news-images/{news}', [FileController::class, 'addPostImage']);
    Route::post('/add-page-images/{page}', [FileController::class, 'addPageImage']);
    //Image delete
    Route::delete('/delete-image/{image}', [FileController::class, 'deleteImage']);
    //Documents
    //Set
    Route::post('/competitor-documents/{compatitor}', [FileController::class, 'addDocumentCompatitor']);
    Route::post('/special-personnel-documents/{special_personal}', [FileController::class, 'addDocumentSpecialPersonal']);
    Route::post('/competition-documents/{compatition}', [FileController::class, 'addDocumentCompatition']);
    Route::post('/page-documents/{page}', [FileController::class, 'addDocumentPage']);
    Route::post('/news-documents/{news}', [FileController::class, 'addDocumentPost']);
    
    //get
    Route::get('/competitor-documents/{compatitor}', [FileController::class, 'compatitorDocuments']);
    Route::get('/special-personnel-documents/{specialPersonal}', [FileController::class, 'specialPersonalDocuments']);
    //Delete
    Route::delete('/document-delete/{document}', [FileController::class, 'deleteDocument']);


    //Categories
    Route::resource('/categories', CategoriesController::class);
    Route::get('/categories-for-time-table/{competition}', [CategoriesController::class, 'catForTimeTable']);
    //Belts
    Route::post('/belts-bulk-store', [ReusableDataController::class, 'bulkStoreBelts']);
    Route::post('/belts-store', [ReusableDataController::class, 'bulkStore']);
    Route::get('/belts', [ReusableDataController::class, 'index']);
    //Special personal
    Route::resource('/special-personnel', SpecialPersonalsController::class);
    //Special Persona in club
    Route::get('/club-administration/{club}', [ReusableDataController::class, 'getClubsAdministration']);
    Route::post('/club-administration', [ReusableDataController::class, 'clubsAdministration']);
    Route::get('/club-competitors/{club}', [ReusableDataController::class, 'clubCompatitors']);
    //COMPATITION
    Route::resource('/competitions', CompatitionsController::class);
    Route::get('/competition-roles/{competition}', [ReusableDataController::class, 'competitionRoles']);
    Route::get('/registered-clubs', [ReusableDataController::class, 'registeredClubs']);

    //Clubs
    Route::resource('/clubs', ClubsController::class);   
    //Compatitiors
    Route::resource('/competitors', CompatitorsController::class);  
    Route::get('/competitor-results/{competitor}', [ReusableDataController::class, 'getCompatitorResults']);
    //Users control
    Route::resource('/users', UsersController::class);
    //Registration of compatitiors on compatition
    Route::resource('/teams', TeamsController::class);
  
    Route::get('/competition-aplications/{competition}', [RegistrationsController::class, 'index']);
    Route::get('/competition-aplications-filtered-categories/{competition}', [RegistrationsController::class, 'categoriesFiltered']);
    Route::post('/competition-aplications/{competition}', [RegistrationsController::class, 'newStore']);
    Route::post('/competition-single-aplications/{competition}', [RegistrationsController::class, 'store']);
    Route::delete('/competition-aplications/{registration}', [RegistrationsController::class, 'destroy']);
    //Pool table 
    Route::get('/pools', [PoolsController::class, 'index']);
    Route::patch('/pools/{pool}', [PoolsController::class, 'updatePool']);
    Route::patch('/team-pools/{poolTeam}', [PoolsController::class, 'updatePoolTeam']);
    //Route::put('/pools/{compatition}', [PoolsController::class, 'updateBatch']);
    Route::post('/pools-automated/{compatition}', [PoolsController::class, 'automatedStore']);
    //Timetable
    Route::get('/time-table/{competition}', [TimeTablesController::class, 'index']);
    Route::get('/time-table-one/{time_table}', [TimeTablesController::class, 'show']);
    Route::post('/time-table', [TimeTablesController::class, 'store']);
    Route::patch('/time-table', [TimeTablesController::class, 'updateTime']);


    Route::post('/time-table-update/{time_table}', [TimeTablesController::class, 'updateTime']);
    //Roles
    //Roles delete
    Route::delete('/role/{roles}', [ReusableDataController::class, 'deleteRole']);
    //Roles get
    Route::get('/special-personal-competition/{specPersonnels}', [ReusableDataController::class, 'specPersonnelCompetitionRoles']);
    Route::get('/special-personal-roles/{specPersonnels}', [ReusableDataController::class, 'specPersonnelRoles']);
    //Compatition
    Route::post('/competition-personnel/{competition}', [CompatitionsController::class, 'specialPersonalOnCompatition']);
    //Posts
    Route::get('/news', [PostsController::class, 'index']);
    Route::get('/news/{news}', [PostsController::class, 'show']);
    Route::post('/news', [PostsController::class, 'store']);
    Route::post('/news/{news}', [PostsController::class, 'update']);
    Route::delete('/news/{news}', [PostsController::class, 'destroy']);
    Route::get('/news-components/{news}', [PostsController::class, 'postComponents']);
    //Pages
    //Route::resource('/pages', PagesController::class);
    Route::get('/pages', [PagesController::class, 'index']);
    Route::get('/pages/{page}', [PagesController::class, 'show']);
    Route::post('/pages', [PagesController::class, 'store']);
    Route::post('/pages/{page}', [PagesController::class, 'update']);
    Route::delete('/pages/{page}', [PagesController::class, 'destroy']);
    Route::get('/page-components/{page}', [PagesController::class, 'pageComponents']);

    //Component
    Route::post('page-components/{page}', [ComponentController::class, 'storePageComponent']);
    Route::post('news-components/{news}', [ComponentController::class, 'storePostComponent']);

    Route::delete('component/{component}', [ComponentController::class, 'destroy']);
    Route::patch('component/{component}', [ComponentController::class, 'update']);
    Route::get('component/{component}', [ComponentController::class, 'show']);
    //component file managament
    Route::post('component-document/{component}', [FileController::class, 'storeComponentDocs']);
    Route::get('component-document/{component}', [ComponentController::class, 'getComponentDocs']);
    Route::post('component-roles/{component}', [ReusableDataController::class, 'storeComponentRole']);
    Route::get('component-roles/{component}', [ComponentController::class, 'getComponentRole']);
    Route::post('component-image/{component}', [FileController::class, 'storeComponentImage']);
    Route::get('component-image/{component}', [ComponentController::class, 'getComponentImage']);


    //Compatition filtering data
    //Category
    Route::get('/competition-categories/{competition}', [CompatitionsController::class, 'categories']);
 
 
});
//testing



Route::group(['prefix' => 'v1', 'middleware' => ['auth:sanctum', 'abilities:reset']], function () {
    Route::get('/validate-token', [AuthController::class, 'checkToken']);
    Route::post('/reset-password', [AuthController::class, 'resetForgotenPassword']);
    
});
Route::group(['prefix' => 'v1', 'middleware' => ['auth:sanctum', 'abilities:admin']], function () {
    Route::post('/create-user', [AuthController::class, 'create_user']);
    
});