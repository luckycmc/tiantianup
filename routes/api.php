<?php

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\BillController;
use App\Http\Controllers\CommonController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\IndexController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\ParentController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\SystemController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WithdrawController;
use App\Models\Message;
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
    $api->group(['prefix'=>'common'],function ($api){
        $api->any('{action}', function (Request $request, CommonController $index, $action) {
            return $index->$action($request);
        });
    });
    $api->group(['prefix'=>'system'],function ($api){
        $api->any('{action}', function (Request $request, SystemController $index, $action) {
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
    $api->group(['prefix'=>'course','middleware' => 'api.jwt.auth'],function ($api){
        $api->any('{action}', function (Request $request, CourseController $index, $action) {
            return $index->$action($request);
        });
    });
    $api->group(['prefix'=>'tag','middleware' => 'api.jwt.auth'],function ($api){
        $api->any('{action}', function (Request $request, TagController $index, $action) {
            return $index->$action($request);
        });
    });
    $api->group(['prefix'=>'organization','middleware' => ['api.jwt.auth']],function ($api){
        $api->any('{action}', function (Request $request, OrganizationController $index, $action) {
            return $index->$action($request);
        });
    });
    $api->group(['prefix'=>'activity','middleware' => ['api.jwt.auth']],function ($api){
        $api->any('{action}', function (Request $request, ActivityController $index, $action) {
            return $index->$action($request);
        });
    });
    $api->group(['prefix'=>'payment','middleware' => ['api.jwt.auth']],function ($api){
        $api->any('{action}', function (Request $request, PaymentController $index, $action) {
            return $index->$action($request);
        });
    });
    $api->group(['prefix'=>'team','middleware' => ['api.jwt.auth']],function ($api){
        $api->any('{action}', function (Request $request, TeamController $index, $action) {
            return $index->$action($request);
        });
    });
    $api->group(['prefix'=>'bill','middleware' => ['api.jwt.auth']],function ($api){
        $api->any('{action}', function (Request $request, BillController $index, $action) {
            return $index->$action($request);
        });
    });
    $api->group(['prefix'=>'withdraw','middleware' => ['api.jwt.auth']],function ($api){
        $api->any('{action}', function (Request $request, WithdrawController $index, $action) {
            return $index->$action($request);
        });
    });
    $api->group(['prefix'=>'parent','middleware' => ['api.jwt.auth']],function ($api){
        $api->any('{action}', function (Request $request, ParentController $index, $action) {
            return $index->$action($request);
        });
    });
    $api->group(['prefix'=>'message','middleware' => ['api.jwt.auth']],function ($api){
        $api->any('{action}', function (Request $request, MessageController $index, $action) {
            return $index->$action($request);
        });
    });
});
