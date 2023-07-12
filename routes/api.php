<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\EstimateController;
use App\Http\Controllers\API\ClientController;
use App\Http\Controllers\API\SecurityController;
use App\Http\Controllers\DeliveryNoteController;
use App\Http\Controllers\PaymentMethodController;
use App\Http\Controllers\API\FournisseurController;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('register', [SecurityController::class, 'store']);
Route::post('login', [SecurityController::class, 'index']);


Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::post('/fournisseurs', [FournisseurController::class, 'index']);
    Route::post('/fournisseurs', [FournisseurController::class, 'store']);
    Route::get('/fournisseurs/{id}', [FournisseurController::class, 'show']);
    Route::put('/fournisseurs/{id}', [FournisseurController::class, 'update']);
    Route::delete('/fournisseurs/{id}', [FournisseurController::class, 'destroy']);
});

Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::get('/clients', [ClientController::class, 'index']);
    Route::post('/clients', [ClientController::class, 'store']);
    Route::get('/clients/{id}', [ClientController::class, 'show']);
    Route::put('/clients/{id}', [ClientController::class, 'update']);
    Route::delete('/clients/{id}', [ClientController::class, 'destroy']);
    Route::post('/clients/import-clients', [ClientController::class, 'importClients']);
});
Route::get('/export-clients', [ClientController::class, 'exportClients']);


Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::get('/estimates', [EstimateController::class, 'index']);
    Route::post('/estimates', [EstimateController::class, 'store']);
    Route::get('/estimates/{id}', [EstimateController::class, 'show']);
    Route::put('/estimates/{id}', [EstimateController::class, 'update']);
    Route::delete('/estimates/{id}', [EstimateController::class, 'destroy']);
    Route::post('/estimates/next-no', [EstimateController::class, 'nextNo']);
    Route::post('/estimates/to-invoice/{id}', [EstimateController::class, 'toInvoice']);
});
Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::get('/invoices', [InvoiceController::class, 'index']);
    Route::post('/invoices', [InvoiceController::class, 'store']);
    Route::get('/invoices/{id}', [InvoiceController::class, 'show']);
    Route::put('/invoices/{id}', [InvoiceController::class, 'update']);
    Route::delete('/invoices/{id}', [InvoiceController::class, 'destroy']);
    Route::post('/invoices/to-delivery-note/{id}', [InvoiceController::class, 'toDeliveryNote']);
});
Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::get('/delivery-notes', [DeliveryNoteController::class, 'index']);
    Route::post('/delivery-notes', [DeliveryNoteController::class, 'store']);
    Route::get('/delivery-notes/{id}', [DeliveryNoteController::class, 'show']);
    Route::put('/delivery-notes/{id}', [DeliveryNoteController::class, 'update']);
    Route::delete('/delivery-notes/{id}', [DeliveryNoteController::class, 'destroy']);
    Route::get('/delivery-notes/next-no', [DeliveryNoteController::class, 'nextNo']);
});
Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::get('/payments', [PaymentController::class, 'index']);
    Route::post('/payments', [PaymentController::class, 'store']);
    Route::get('/payments/{id}', [PaymentController::class, 'show']);
    Route::put('/payments/{id}', [PaymentController::class, 'update']);
    Route::delete('/payments/{id}', [PaymentController::class, 'destroy']);
});

Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::get('/payment_methods', [PaymentMethodController::class, 'index']);
    Route::post('/payment_methods', [PaymentMethodController::class, 'store']);
    Route::get('/payment_methods/{id}', [PaymentMethodController::class, 'show']);
    Route::put('/payment_methods/{id}', [PaymentMethodController::class, 'update']);
    Route::delete('/payment_methods/{id}', [PaymentMethodController::class, 'destroy']);
});
