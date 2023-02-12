<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoriesController;
use App\Http\Controllers\ClubsController;
use App\Http\Controllers\CompatitorsController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\ReusableDataController;
use App\Http\Controllers\SpecialPersonalsController;
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


});


Route::group(['prefix' => 'v1', 'middleware' => ['auth:sanctum']], function () {
    
    //All auth users
    Route::post('/logout', [AuthController::class, 'logout']); 
    Route::post('/change-password/{user}',[AuthController::class, 'changePassword']);

    //File managing
    //Images
    Route::post('/compatitor-image/{compatitor}', [FileController::class, 'setCompatitorImage']);
    Route::post('/club-image/{club}', [FileController::class, 'setClubImage']);
    Route::post('/special-personal-image/{personal}', [FileController::class, 'setSpecPersonImage']);

    //Documents
    //Get
    Route::get('/compatitor-documents/{compatitor}', [FileController::class, 'getCompatitorDocuments']);
    Route::get('/special-personal-documents/{special_personal}', [FileController::class, 'getSpecialPersonalDocuments']);
    //Set
    Route::post('/compatitor-documents/{compatitor}', [FileController::class, 'addDocumentCompatitor']);
    Route::post('/special-personal-documents/{special_personal}', [FileController::class, 'addDocumentSpecialPersonal']);
    //Delete
    Route::post('/compatitor-documents-delete/{compatitor}', [FileController::class, 'deleteDocumentCompatitor']);
    Route::post('/special-personal-documents-delete/{special_personal}', [FileController::class, 'deleteDocumentSpecialPersonal']);

    //Categories
    Route::resource('/categories', CategoriesController::class);

    //Reusabe data get
    //Belts
    Route::post('/belts/store', [ReusableDataController::class, 'store']);
    Route::get('/belts', [ReusableDataController::class, 'index']);

    //Special personal
    Route::resource('/special-personal', SpecialPersonalsController::class);
    //Special Persona in club
    Route::post('/club-administration', [ClubsController::class, 'clubsAdministration']);

    Route::get('/clubs', [ClubsController::class, 'protected']);   
    Route::post('/clubs', [ClubsController::class, 'store']);
    Route::get('/clubs/{club}', [ClubsController::class, 'show_protected']);
    Route::patch('/clubs/{club}', [ClubsController::class, 'update']);
    Route::delete('/clubs/{club}', [ClubsController::class, 'destroy']);

    Route::get('/compatitors', [CompatitorsController::class, 'protected']);  
    Route::post('/compatitors', [CompatitorsController::class, 'store']);
    Route::get('/compatitors/{compatitor}', [CompatitorsController::class, 'show_protected']);  
    Route::patch('/compatitors/{compatitor}', [CompatitorsController::class, 'update']);
    Route::delete('/compatitors/{compatitor}', [CompatitorsController::class, 'destroy']);
    
    //Users control
    Route::resource('/users', UsersController::class);


});



Route::group(['prefix' => 'v1', 'middleware' => ['auth:sanctum', 'abilities:admin']], function () {
    Route::post('/create-user', [AuthController::class, 'create_user']);
    
});