<?php

use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;
use Dcat\Admin\Admin;

Admin::routes();

Route::group([
    'prefix'     => config('admin.route.prefix'),
    'namespace'  => config('admin.route.namespace'),
    'middleware' => config('admin.route.middleware'),
], function (Router $router) {

    $router->get('/', 'HomeController@index');
    $router->prefix('/api')->group(function ($router) {
        $router->get('/admin_users', 'AuthController@admin_users');
        $router->get('/province', 'RegionController@province');
        $router->get('/city', 'RegionController@city');
        $router->get('/organ_type', 'OrganTypeController@list');
        $router->get('/nature', 'TeachingMethodController@list');
        $router->get('/education', 'EducationController@list');
    });
    $router->resource('/organization','OrganizationController');
    $router->resource('/teacher_info','TeacherInfoController');
    $router->resource('/teacher_real_auth','TeacherRealAuthController');
    $router->resource('/teacher_image','TeacherImageController');
    $router->resource('/teacher_education','TeacherEducationController');
    $router->resource('/consults','ConsultController');
    $router->resource('/course/role/{role}','CourseController');

});
