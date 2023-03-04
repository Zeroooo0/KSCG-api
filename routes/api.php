<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoriesController;
use App\Http\Controllers\ClubsController;
use App\Http\Controllers\CompatitionsController;
use App\Http\Controllers\CompatitorsController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\PagesController;
use App\Http\Controllers\PoolsController;
use App\Http\Controllers\PostsController;
use App\Http\Controllers\RegistrationsController;
use App\Http\Controllers\ReusableDataController;
use App\Http\Controllers\SpecialPersonalsController;
use App\Http\Controllers\TimeTablesController;
use App\Http\Controllers\UsersController;

use Illuminate\Support\Facades\Route;



Route::get('file/{path}', [FileController::class, 'getFile'])->where('path', '.*');

Route::group(['prefix' => 'v1/public'], function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::get('/clubs', [ClubsController::class, 'public']);  
    Route::get('/clubs/{club}', [ClubsController::class, 'show_public']);  
    Route::get('/compatitors', [CompatitorsController::class, 'public']);
    Route::get('/compatitors/{compatitor}', [CompatitorsController::class, 'show_public']);
    Route::get('/posts', [PostsController::class, 'public']);
    Route::get('/pages', [PagesController::class, 'public']);
});

Route::group(['prefix' => 'v1', 'middleware' => ['auth:sanctum']], function () {    
    //All auth users
    Route::post('/logout', [AuthController::class, 'logout']); 
    Route::post('/change-password/{user}',[AuthController::class, 'changePassword']);
    //File managing
    //Images update
    Route::post('/compatitor-image/{compatitor}', [FileController::class, 'setCompatitorImage']);
    Route::post('/club-image/{club}', [FileController::class, 'setClubImage']);
    Route::post('/special-personal-image/{personal}', [FileController::class, 'setSpecPersonImage']);
    Route::post('/compatition-image/{compatition}', [FileController::class, 'setCompatitionImage']);
    //Image add
    Route::post('/add-post-images/{post}', [FileController::class, 'addPostImage']);
    Route::post('/add-page-images/{page}', [FileController::class, 'addPageImage']);
    //Image delete
    Route::delete('/delete-image/{image}', [FileController::class, 'deleteImage']);
    //Documents
    //Set
    Route::post('/compatitor-documents/{compatitor}', [FileController::class, 'addDocumentCompatitor']);
    Route::post('/special-personal-documents/{special_personal}', [FileController::class, 'addDocumentSpecialPersonal']);
    Route::post('/compatition-documents/{compatition}', [FileController::class, 'addDocumentCompatition']);
    //Delete
    Route::post('/document-delete/{document}', [FileController::class, 'deleteDocument']);


    //Categories
    Route::resource('/categories', CategoriesController::class);
    //Belts
    Route::post('/belts', [ReusableDataController::class, 'bulkStoreBelts']);
    //Route::get('/belts', [ReusableDataController::class, 'index']);
    //Special personal
    Route::resource('/special-personal', SpecialPersonalsController::class);
    //Special Persona in club
    Route::post('/club-administration', [ReusableDataController::class, 'clubsAdministration']);
    //COMPATITION
    Route::resource('/compatitions', CompatitionsController::class);
    //Clubs
    Route::resource('/clubs', ClubsController::class);   
    //Compatitiors
    Route::resource('/compatitors', CompatitorsController::class);  
    //Users control
    Route::resource('/users', UsersController::class);
    //Registration of compatitiors on compatition
    Route::resource('/registrations', RegistrationsController::class);
    //Pull table 
    Route::resource('/pools', PoolsController::class);
    Route::put('/pools/{compatition}', [PoolsController::class, 'updateBatch']);
    //Timetable
    Route::resource('/time-table', TimeTablesController::class);

    //Roles delete
    Route::delete('/role/{roles}', [ReusableDataController::class, 'deleteRole']);
    //Compatition
    Route::post('/compatition-personal/{compatition}', [CompatitionsController::class, 'specialPersonalOnCompatition']);
    //Posts
    Route::resource('/posts', PostsController::class);
    //Pages
    Route::resource('/pages', PagesController::class);





    //Compatition filtering data
    //Category
    Route::get('/compatition-categories/{compatition}', [CompatitionsController::class, 'categories']);
 
});



Route::group(['prefix' => 'v1', 'middleware' => ['auth:sanctum', 'abilities:admin']], function () {
    Route::post('/create-user', [AuthController::class, 'create_user']);
    
});