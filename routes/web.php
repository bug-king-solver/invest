<?php

use App\Http\Controllers\AppController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\InvoicesController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

/* using middleware */

Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::get('invoice/{invoice_id}', [InvoicesController::class, 'getInvoice']);
});


Route::get('/{any}', [AppController::class, 'index'])->where('any', '.*');


