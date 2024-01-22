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
        $router->get('/operational_city', 'RegionController@operational_city');
        $router->get('/education', 'EducationController@list');
        $router->get('/training_type', 'ConstantController@training_type');
        $router->get('/organ_type', 'ConstantController@organ_type');
        $router->get('/nature', 'ConstantController@nature');
    });
    $router->resource('/organization','OrganizationController');
    $router->resource('/user','UserController');
    $router->resource('/teacher_info','TeacherInfoController');
    $router->resource('/teacher_real_auth','TeacherRealAuthController');
    $router->resource('/teacher_image','TeacherImageController');
    $router->resource('/teacher_education','TeacherEducationController');
    $router->resource('/teacher_cert','TeacherCertController');
    $router->resource('/teacher_career','TeacherCareerController');
    $router->resource('/consults','ConsultController');
    $router->resource('/all_consults','AllConsultController');
    $router->resource('/teacher_course','TeacherCourseController');
    $router->resource('/student_course','StudentCourseController');
    $router->resource('/intermediary_course','IntermediaryCourseController');
    $router->resource('/withdraw','WithdrawController');
    $router->resource('/invite_new_activity','InviteNewActivityController');
    $router->resource('/teacher_register_activity','TeacherRegisterActivityController');
    $router->resource('/deal_activity','DealActivityController');
    $router->resource('/activity','ActivityController');
    $router->resource('/bill','BillController');
    $router->resource('/user_course','UserCourseController');
    $router->resource('/teacher_deal','TeacherDealServicePriceController');
    $router->resource('/teacher_organ_deal','TeacherOrganDealServicePriceController');
    $router->resource('/look_teacher','LookTeacherServicePriceController');
    $router->resource('/entry','EntryServicePriceController');
    $router->resource('/course_contact','CourseContactServicePriceController');
    $router->resource('/base_information','BaseInformationController');
    $router->resource('/system_images','SystemImageController');
    $router->resource('/banner','BannerController');
    $router->resource('/agreement','AgreementController');
    $router->resource('/constant','ConstantController');
    $router->resource('/system_message','SystemMessageController');
    $router->resource('/notice','NoticeController');
    $router->resource('/region','RegionController');
    $router->resource('/student_course_setting','StudentCourseSettingController');
    $router->resource('/teacher_course_setting','TeacherCourseSettingController');
    $router->resource('/platform_message','PlatformMessageController');
    $router->resource('/area','AreaController');
    $router->resource('/tag','TagController');
});
