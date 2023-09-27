<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\User;
use App\Models\UserCourse;
use Carbon\Carbon;
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
        $select_field = ['courses.*','organizations.name as organ_name','organizations.longitude','organizations.latitude'];
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
        if (isset($data['district_id'])) {
            $where[] = ['organizations.district_id','=',$data['district_id']];
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
        if (isset($data['filter_price_min']) && isset($data['filter_price_max'])) {
            $where[] = ['courses.class_price','>=',$data['filter_price_min']];
            $where[] = ['courses.class_price','<=',$data['filter_price_max']];
        }
        if (isset($data['grade'])) {
            $where[] = ['courses.grade','=',$data['grade']];
        }
        if (isset($data['filter_distance_min']) && isset($data['filter_price_max'])) {
            $distance_expr = "6371 * acos(cos(radians($latitude)) * cos(radians(latitude)) * cos(radians(longitude) - radians($longitude)) + sin(radians($latitude)) * sin(radians(latitude)))";
            $where[] = [DB::raw($distance_expr),'>=',$data['filter_distance_min']];
            $where[] = [DB::raw($distance_expr),'<=',$data['filter_price_max']];
        }
        // 当前用户
        $user = Auth::user();
        if (isset($data['is_entry'])) {
            $user_courses = DB::table('user_courses')->where('user_id',$user->id)->select('course_id')->get();
            $course_arr = $user_courses->pluck('course_id')->toArray();
            if ($data['is_entry'] == 1) {
                $where[] = [function ($query) use ($course_arr) {
                    $query->whereIn('courses.id',$course_arr);
                }];
            } else {
                $where[] = [function ($query) use ($course_arr) {
                    $query->whereNotIn('courses.id',$course_arr);
                }];
            }
        }
        if ($user->role == 3) {
            $where[] = ['courses.role','=',3];
        }
        if (isset($data['is_platform'])) {
            $where[] = ['courses.adder_role','=',3];
        }
        $result = Course::leftJoin('organizations','courses.organ_id','=','organizations.id')
            ->select($select_field)
            ->where($where)
            ->orderBy($sort_field,$order)
            ->paginate($page_size);

        foreach ($result as $v) {
            // 是否已报名
            $v->is_entry = UserCourse::where(['user_id' => $user->id,'course_id' => $v->id])->exists();
            $v->distance = calculate_distance($latitude,$longitude,$v->latitude,$v->longitude);
            if ($v->adder_role == 0) {
                // 是否查看
                $v->is_show = $v->teacher_course->where('status',1)->isNotEmpty();
            }
        }
        return $this->success('课程列表',$result);
    }

    /**
     * 联系商家
     * @return \Illuminate\Http\JsonResponse
     */
    public function entry()
    {
        $data = \request()->all();
        $course_id = $data['course_id'];
        $course_info = Course::find($course_id);
        if (!$course_info) {
            return $this->error('课程不存在');
        }
        // 当前用户
        $user = Auth::user();
        $insert_data = [
            'user_id' => $user->id,
            'course_id' => $course_id,
            'role' => $user->role,
            'created_at' => Carbon::now()
        ];
        // 保存数据
        $result = DB::table('user_courses')->insert($insert_data);
        if (!$result) {
            return $this->error('联系机构失败');
        }
        return $this->success('稍后会有商家给您致电');
    }
}
