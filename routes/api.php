<?php

use App\Http\Controllers\API\UserController;
use App\Http\Controllers\RoleController;
use App\Mail\SendEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
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

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware(['auth:sanctum'])->group(function(){
    Route::apiResource('users', UserController::class);

    Route::resource('roles', RoleController::class);
});

Route::get('send-email', function(){
    $email = new SendEmail();
    Mail::to('tonyseptian32@gmail.com')->send($email);
});
