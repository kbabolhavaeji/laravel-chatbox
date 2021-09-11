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
Route::post('/receive', [ChatController::class, 'fetchMessages'])->name('receive');
Route::get('/public/{room}', [ChatController::class, 'publicChat'])->name('room.public');
Route::get('/private/{room}', [ChatController::class, 'privateChat'])->name('room.private');
Route::post('/search', [ChatController::class, 'search'])->name('search');
