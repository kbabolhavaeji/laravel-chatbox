<?php

use App\Http\Controllers\ChatController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

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

Auth::routes();

Route::get('/', [ChatController::class,'index'])->name('show');
Route::post('/send', [ChatController::class, 'sendMessage'])->name('send');
Route::get('/receive', [ChatController::class, 'fetchMessages'])->name('receive');
Route::get('/public/{room}', [ChatController::class, 'loadRoom'])->name('chat.room');
Route::get('/private/{user}', [ChatController::class, 'privateChat'])->name('private.room');
