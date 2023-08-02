<?php

use App\Http\Controllers\IndexController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\UserController;
use Dingo\Api\Routing\Router;
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

$api = app(Router::class);
$api->version('v1', function ($api) {
    $api->group(['prefix'=>'login'],function ($api){
        $api->any('{action}', function (Request $request, LoginController $index, $action) {
            return $index->$action($request);
        });
    });
    $api->group(['prefix'=>'user','middleware' => 'api.jwt.auth'],function ($api){
        $api->any('{action}', function (Request $request, UserController $index, $action) {
            return $index->$action($request);
        });
    });
    $api->group(['prefix'=>'index','middleware' => 'api.jwt.auth'],function ($api){
        $api->any('{action}', function (Request $request, IndexController $index, $action) {
            return $index->$action($request);
        });
    });
    $api->group(['prefix'=>'teacher','middleware' => 'api.jwt.auth'],function ($api){
        $api->any('{action}', function (Request $request, TeacherController $index, $action) {
            return $index->$action($request);
        });
    });
});
