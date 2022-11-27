<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DebitCardController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::controller(DebitCardController::class)->group(function () {
    Route::get('/debit-cards', 'index')->name('debitCard');
    Route::post('/debit-cards', 'store')->name('createDebitCard');
    Route::get('/debitCardTest', 'test')->name('debitCardTest');
    Route::post('dc/create', 'create');
    Route::put('dc/update/{id}', 'update');
    Route::delete('dc/delete', 'delete');
}); 