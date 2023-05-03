<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EstimateController;
use App\Http\Controllers\API\ClientController;
use App\Http\Controllers\API\SecurityController;
use App\Http\Controllers\API\FournisseurController;
use App\Http\Controllers\DeliveryNoteController;
use App\Http\Controllers\InvoiceController;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('register', [SecurityController::class, 'store']);
Route::post('login', [SecurityController::class, 'index']);


Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::post('/fournisseur',[FournisseurController::class,'index']);
    Route::post('/fournisseur/add',[FournisseurController::class,'store']);
    Route::get('/fournisseur/show/{id}',[FournisseurController::class,'show']);
    Route::put('/fournisseur/update/{id}',[FournisseurController::class,'update']);
    Route::delete('/fournisseur/{id}',[FournisseurController::class,'destroy']);
});

Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::post('/client',[ClientController::class,'index']);
    Route::post('/client/add',[ClientController::class,'store']);
    Route::get('/client/show/{id}',[ClientController::class,'show']);
    Route::put('/client/update/{id}',[ClientController::class,'update']);
    Route::delete('/client/{id}',[ClientController::class,'destroy']);
});

Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::get('/estimate',[EstimateController::class,'index']);
    Route::post('/estimate/add',[EstimateController::class,'store']);
    Route::get('/estimate/show/{id}',[EstimateController::class,'show']);
    Route::put('/estimate/update/{id}',[EstimateController::class,'update']);
    Route::delete('/estimate/{id}',[EstimateController::class,'destroy']);
    Route::get('/estimate/next-no',[EstimateController::class,'nextNo']);
});
Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::get('/invoice',[InvoiceController::class,'index']);
    Route::post('/invoice/add',[InvoiceController::class,'store']);
    Route::get('/invoice/show/{id}',[InvoiceController::class,'show']);
    Route::put('/invoice/update/{id}',[InvoiceController::class,'update']);
    Route::delete('/invoice/{id}',[InvoiceController::class,'destroy']);
});
Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::get('/delivery-notes',[DeliveryNoteController::class,'index']);
    Route::post('/delivery-notes/add',[DeliveryNoteController::class,'store']);
    Route::get('/delivery-notes/show/{id}',[DeliveryNoteController::class,'show']);
    Route::put('/delivery-notes/update/{id}',[DeliveryNoteController::class,'update']);
    Route::delete('/delivery-notes/{id}',[DeliveryNoteController::class,'destroy']);
    Route::get('/delivery-notes/next-no',[DeliveryNoteController::class,'nextNo']);
});