<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\DeliverLog;
use App\Models\Message;
use App\Models\Region;
use App\Models\SystemMessage;
use App\Models\TeacherCourseOrder;
use App\Models\User;
use App\Models\UserCourse;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Overtrue\EasySms\EasySms;
use Overtrue\EasySms\Exceptions\Exception;
use Overtrue\EasySms\Exceptions\NoGatewayAvailableException;
use Overtrue\EasySms\PhoneNumber;

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
        $distance_expr = "
            (
                6371 * acos(
                    cos(radians({$latitude})) *
                    cos(radians(courses.latitude)) *
                    cos(radians(courses.longitude) - radians({$longitude})) +
                    sin(radians({$latitude})) *
                    sin(radians(courses.latitude))
                )
            ) AS distance";
        $select_field = ['courses.*','organizations.name as organ_name',DB::raw($distance_expr)];
        if (isset($data['sort_price'])) {
            $sort_field = 'courses.class_price';
        } else if (isset($data['sort_distance'])) {
            $sort_field = 'distance';
        }
        $order = $data['order'] ?? 'desc';
        // 筛选
        // 当前城市
        $location_info = get_location($longitude,$latitude);
        $city = $location_info['city'];
        $city_id = Region::where('region_name',$city)->value('id');

        $where = [];
        $where[] = ['courses.city','=',$city_id];
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
        $user = User::find(9);
        if (isset($data['city'])) {
            $id = Region::where('region_name',$data['city'])->value('id');
            $where[] = ['courses.city','=',$id];
        }
        // dd($where);
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
        if ($user->role == 1  || $user->role == 2) {
            $where[] = ['courses.role','=',1];
        }
        if ($user->role == 2 && !isset($data['is_platform'])) {
            $where[] = ['courses.adder_role','=',4];
        }
        if (isset($data['is_platform'])) {
            $where[] = ['courses.adder_role','=',0];
            if (isset($data['is_show'])) {
                $order_arr = DeliverLog::where(['user_id' => $user->id,'pay_status' => 1])->distinct()->pluck('course_id');
                if ($data['is_show'] == 1) {
                    $where[] = [function ($query) use ($order_arr) {
                        $query->whereIn('courses.id',$order_arr);
                    }];
                }
            }
            if (isset($data['province'])) {
                $id = Region::where('region_name',$data['province'])->value('id');
                $where[] = ['courses.province','=',$id];
            }
            if (isset($data['district'])) {
                $id = Region::where('region_name',$data['district'])->value('id');
                $where[] = ['courses.district','=',$id];
            }
            if (isset($data['gender'])) {
                $where[] = ['courses.gender','=',$data['gender']];
            }
            if (isset($data['created_at_start']) && isset($data['created_at_end'])) {
                $where[] = ['courses.created_at','>=',$data['created_at_start']];
                $where[] = ['courses.created_at','<=',$data['created_at_end']];
            }
        }
        $result = Course::leftJoin('organizations','courses.organ_id','=','organizations.id')
            ->select($select_field)
            ->where($where)
            ->where('courses.status','=',1)
            ->orderBy($sort_field,$order)
            ->paginate($page_size);

        foreach ($result as $v) {
            // 是否已报名
            $v->is_entry = UserCourse::where(['user_id' => $user->id,'course_id' => $v->id])->exists();
            $v->distance = round($v->distance,2);
            if ($v->adder_role == 0) {
                // 是否查看
                $v->is_show = DeliverLog::where(['user_id' => $user->id,'course_id' => $v->id,'pay_status' => 1])->exists();
            }
            $v->province = $v->province_info->region_name;
            $v->city = $v->city_info->region_name;
            $v->district = $v->district_info->region_name;
            if ($v->adder_role == 0) {
                $v->class_date = $v->platform_class_date;
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
        $config = config('services.sms');
        $data = \request()->all();
        $course_id = $data['course_id'] ?? 0;
        $course_info = Course::find($course_id);
        if (!$course_info) {
            return $this->error('课程不存在');
        }
        // 当前用户
        $user = Auth::user();
        $out_trade_no = app('snowflake')->id();
        $user_city = Region::where('id',$user->city_id)->value('region_name');
        $user_province = Region::where('id',$user->province_id)->value('region_name');
        $user_district = Region::where('id',$user->district_id)->value('region_name');
        $amount = get_service_price(3, $user_province,$user_city,$user_district);
        // $amount = 0.01;
        $insert_data = [
            'user_id' => $user->id,
            'course_id' => $course_id,
            'role' => $user->role,
            'out_trade_no' => $out_trade_no,
            'amount' => $amount,
            'status' => 0,
            'created_at' => Carbon::now()
        ];
        // 保存数据
        $result = DB::table('user_courses')->insert($insert_data);
        $course_info->entry_number += 1;
        $course_info->save();
        if (!$result) {
            return $this->error('联系机构失败');
        }
        $organ_user = User::find($course_info->adder_id);
        // 发送通知
        if (SystemMessage::where('action',16)->value('site_message') == 1) {
            (new Message())->saveMessage($organ_user->id,$user->id,'报名信息','有家长/学生报名了您的课程',$course_id,0,4);
        }
        if (SystemMessage::where('action',16)->value('text_message') == 1) {
            // 发送短信
            $easySms = new EasySms($config);
            try {
                $number = new PhoneNumber($organ_user->mobile);
                $easySms->send($number,[
                    'content'  => "【添添学】有家长/学生报名了您的课程",
                ]);
            } catch (Exception|NoGatewayAvailableException $exception) {
                return $this->error($exception->getResults());
            }
        }
        return $this->success('稍后会有商家给您致电');
    }
}
