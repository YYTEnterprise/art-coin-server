<?php

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

// Public API
Route::middleware(['api'])->group(function () {
    Route::post('/register', 'UserController@register');
    Route::post('/login', 'UserController@login');

    Route::prefix('/market/products')->group(function () {
        Route::get('/', 'MarketController@index');
        Route::get('/{id}', 'MarketController@show');
    });
});

// User Api
Route::middleware(['auth:api'])->group(function () {
    Route::prefix('products')->group(function () {
        Route::get('/', 'ProductController@index');
        Route::get('/{id}', 'ProductController@show');
        Route::post('/', 'ProductController@store');
        Route::put('/{id}', 'ProductController@update');
        Route::delete('/{id}', 'ProductController@destroy');
    });

    Route::prefix('users')->group(function () {
        Route::post('/follow', 'UserFollowController@follow');
        Route::post('/unfollow', 'UserFollowController@unfollow');
        Route::get('/followers', 'UserFollowController@followerList');
        Route::get('/followings', 'UserFollowController@followingLIst');
    });
});
