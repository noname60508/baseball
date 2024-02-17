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

Route::get('hello', function () {
    return "hello";
});

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::group(['prefix' => 'v1'], function ($router) {
    /*-------------------使用者----------------------*/
    Route::group(['prefix' => 'User'], function ($router) {
        // 新增帳號
        Route::post('/register', 'Account\AuthController@register');
        // 刪除帳號
        Route::post('/deleteUser', 'Account\AuthController@deleteUser');
        // 登入
        Route::post('/login', 'Account\AuthController@login');
        // 登出
        Route::get('/logout', 'Account\AuthController@logout');
        // 使用者清單
        Route::post('/userList', 'Account\AuthController@userList');
        // 修改帳號
        Route::post('/updateUser', 'Account\AuthController@updateUser');
        // token資料
        Route::get('/userProfile', 'Account\AuthController@userProfile');
    });
});
