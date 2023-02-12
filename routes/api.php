<?php

use App\Http\Controllers\ConnectionController;
use App\Http\Controllers\NodeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::prefix('nodes')->group(function(){
    Route::post('/update',[NodeController::class,'update']);
    Route::put('/create',[NodeController::class,'create']);
    Route::delete('/delete/{id}',[NodeController::class,'delete']);
    Route::get('/list',[NodeController::class,'list']);
});
Route::prefix('connections')->group(function(){
    Route::post('/update',[ConnectionController::class,'update']);
    Route::put('/create',[ConnectionController::class,'create']);
    Route::delete('/delete/{id}',[ConnectionController::class,'delete']);
    Route::get('/list',[ConnectionController::class,'list']);
});
