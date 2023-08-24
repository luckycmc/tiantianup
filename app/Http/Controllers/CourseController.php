<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\User;
use App\Models\UserCourse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CourseController extends Controller
{
    /**
     * 课程列表
     * @return \Illuminate\Http\JsonResponse
     */
    public function list()
    {
        $data = \request()->all();
        $page_size = $data['page_size'] ?? 10;
        $longitude = $data['longitude'] ?? 0;
        $latitude = $data['latitude'] ?? 0;
        // 排序
        $sort_field = 'courses.created_at';
        $select_field = ['courses.*','organizations.name as organ_name'];
        if (isset($data['sort_price'])) {
            $sort_field = 'courses.class_price';
        } else if (isset($data['sort_distance']) || isset($data['filter_distance'])) {
            $sort_field = 'distance';
            $distance_expr = "
            (
                6371 * acos(
                    cos(radians({$latitude})) * 
                    cos(radians(organizations.latitude)) * 
                    cos(radians(organizations.longitude) - radians({$longitude})) + 
                    sin(radians({$latitude})) * 
                    sin(radians(organizations.latitude))
                )
            ) AS distance";
            $select_field = ['courses.*','organizations.name as organ_name',DB::raw($distance_expr)];
        }
        $order = $data['order'] ?? 'desc';
        // 筛选
        $where = [];
        if (isset($data['city_id'])) {
            $where[] = ['courses.city_id','=',$data['city_id']];
        }
        if (isset($data['fitler_type'])) {
            $where[] = ['courses.type','=',$data['fitler_type']];
        }
        if (isset($data['filter_method'])) {
            $where[] = ['courses.method','=',$data['filter_method']];
        }
        if (isset($data['filter_subject'])) {
            $where[] = ['courses.subject','=',$data['filter_subject']];
        }
        if (isset($data['filter_price'])) {
            $where[] = ['courses.price','>=',$data['filter_price'][0]];
            $where[] = ['courses.price','<=',$data['filter_price'][1]];
        }
        if (isset($data['filter_distance'])) {
            $distance_expr = "6371 * acos(cos(radians($latitude)) * cos(radians(latitude)) * cos(radians(longitude) - radians($longitude)) + sin(radians($latitude)) * sin(radians(latitude)))";
            $where[] = [DB::raw($distance_expr),'>=',$data['filter_distance'][0]];
            $where[] = [DB::raw($distance_expr),'<=',$data['filter_distance'][1]];
        }
        $result = Course::leftJoin('organizations','courses.organ_id','=','organizations.id')
            ->select($select_field)
            ->where($where)
            ->orderBy($sort_field,$order)
            ->paginate($page_size);
        // 当前用户
        $user = Auth::user();
        foreach ($result as $v) {
            // 是否已报名
            $v->is_entry = UserCourse::where(['user_id' => $user->id,'course_id' => $v->id])->exists();
        }
        return $this->success('课程列表',$result);
    }
}
