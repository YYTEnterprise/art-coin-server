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

    Route::post('/products/{id}/likes', 'UserProductLikeController@likeList');
});

// User Api
Route::middleware(['auth:api'])->group(function () {
    Route::prefix('products')->group(function () {
        Route::get('/', 'ProductController@index');
        Route::get('/{id}', 'ProductController@show');
        Route::post('/', 'ProductController@store');
        Route::put('/{id}', 'ProductController@update');
        Route::delete('/{id}', 'ProductController@destroy');

        Route::post('/{id}/onsale', 'ProductController@onsale');
        Route::post('/{id}/offsale', 'ProductController@offsale');

        Route::post('/{id}/like', 'UserProductLikeController@like');
        Route::post('/{id}/unlike', 'UserProductLikeController@unlike');
    });

    Route::prefix('users')->group(function () {
        Route::post('/follow', 'UserFollowController@follow');
        Route::post('/unfollow', 'UserFollowController@unfollow');
        Route::get('/followers', 'UserFollowController@followerList');
        Route::get('/followings', 'UserFollowController@followingLIst');
    });
});
