<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\DeliverLog;
use App\Models\Message;
use App\Models\PlatformMessage;
use App\Models\Region;
use App\Models\SystemMessage;
use App\Models\TeacherCourseOrder;
use App\Models\User;
use App\Models\UserCourse;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
        Log::info('data: ',$data);
        $page_size = $data['page_size'] ?? 10;
        $longitude = $data['longitude'] ?? 0;
        $latitude = $data['latitude'] ?? 0;
        $where = [];
        $or_where = [];
        // 排序
        $sort_field = 'courses.created_at';
        /*if (isset($data['district_id'])) {
            $district_name = Region::where('id',$data['district_id'])->value('region_name');
            $region_info = get_long_lat('','',$district_name,'');
            $longitude = $region_info[0];
            $latitude = $region_info[1];
        }
        if (isset($data['district'])) {
            $region_info = get_long_lat('','',$data['district'],'');
            $longitude = $region_info[0];
            $latitude = $region_info[1];
        }*/
        /*Log::info('longitude: '.$longitude);
        Log::info('latitude: '.$latitude);*/
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
        $select_field = ['courses.*','organizations.name as organ_name'];
        if (isset($data['sort_price'])) {
            $sort_field = 'courses.class_price';
        } /*else if (isset($data['sort_distance'])) {
            $sort_field = 'distance';
        } */else if (isset($data['sort_visit_count'])) {
            $sort_field = 'courses.visit_count';
        } else if (isset($data['sort_buyer_count'])) {
            $sort_field = 'courses.entry_number';
        }
        $order = $data['order'] ?? 'desc';
        // 筛选
        // 当前城市
        /*if (isset($data['longitude']) && isset($data['latitude'])) {
            $location_info = get_location($longitude,$latitude);
            $city = $location_info['city'];
            $city_id = Region::where('region_name',$city)->value('id');

            $where[] = ['courses.city','=',$city_id];
        }*/


        if (isset($data['fitler_type'])) {
            $where[] = $or_where[] = ['courses.type','=',$data['fitler_type']];
        }
        if (isset($data['filter_method'])) {
            $where[] = $or_where[] = ['courses.method','=',$data['filter_method']];
        }
        if (isset($data['filter_subject'])) {
            if (isset($data['is_platform'])) {
                $where[] = ['courses.subject','like','%'.$data['filter_subject'].'%'];
            } else {
                $where[] = ['courses.subject','=',$data['filter_subject']];
            }
        }
        if (isset($data['filter_name'])) {
            $where[] = $or_where[] = [function ($query) use ($data) {
                $query->where('courses.name','like','%'.$data['filter_name'].'%')
                    ->orWhere('courses.number','like','%'.$data['filter_name'].'%');
            }];
        }

        if (isset($data['latitude']) && isset($data['longitude']) && !isset($data['city']) && !isset($data['city_name'])) {
            // 根据经纬度获取省市区
            $location = get_location($data['longitude'],$data['latitude']);
            if (!$location) {
                return $this->error('定位出错');
            }
            $city_id = Region::where('region_name',$location['city'])->value('id');
            $where[] = ['courses.city', '=', $city_id];
        }

        if (isset($data['filter_price_min']) && isset($data['filter_price_max'])) {
            $where[] = ['courses.class_price','>=',$data['filter_price_min']];
            $where[] = ['courses.class_price','<=',$data['filter_price_max']];
        }
        if (isset($data['grade'])) {
            if (isset($data['is_platform'])) {
                $where[] = ['courses.grade','like','%'.$data['grade'].'%'];
            } else {
                $where[] = ['courses.grade','=',$data['grade']];
            }

        }
        /*if (isset($data['filter_distance_min']) && isset($data['filter_distance_max'])) {
            $distance_expr = "6371 * acos(cos(radians($latitude)) * cos(radians(courses.latitude)) * cos(radians(courses.longitude) - radians($longitude)) + sin(radians($latitude)) * sin(radians(courses.latitude)))";
            $where[] = $or_where[] = [DB::raw($distance_expr),'>=',$data['filter_distance_min']];
            $where[] = $or_where[] = [DB::raw($distance_expr),'<=',$data['filter_distance_max']];
        }*/
        // 当前用户
        $user = Auth::user();
        if (isset($data['city_name'])) {
            $id = Region::where('region_name',$data['city_name'])->value('id');
            $where[] = ['courses.city','=',$id];
        }
        if (isset($data['city'])) {
            $id = Region::where('region_name',$data['city'])->value('id');
            $where[] = ['courses.city','=',$id];
        }
        if (isset($data['district_id'])) {
            $where[] = ['courses.district','=',$data['district_id']];
        }
        // dd($where);
        if (isset($data['is_entry'])) {
            $user_courses = DB::table('user_courses')->where('user_id',$user->id)->select('course_id')->get();
            $course_arr = $user_courses->pluck('course_id')->toArray();
            if ($data['is_entry'] == 1) {
                $where[] = $or_where[] = [function ($query) use ($course_arr) {
                    $query->whereIn('courses.id',$course_arr);
                }];
            } else {
                $where[] = $or_where[] = [function ($query) use ($course_arr) {
                    $query->whereNotIn('courses.id',$course_arr);
                }];
            }
        }
        if ($user->role == 3) {
            $where[] = $or_where[] = ['courses.role','=',3];
        }
        if ($user->role == 1  || $user->role == 2) {
            $where[] = $or_where[] = ['courses.role','=',1];
        }
        if ($user->role == 2 && !isset($data['is_platform'])) {
            $where[] = $or_where[] = ['courses.adder_role','=',4];
        }
        if (isset($data['is_platform'])) {
            $where[] = $or_where[] = ['courses.adder_role','=',0];
            if (isset($data['is_show'])) {
                $order_arr = DeliverLog::where(['user_id' => $user->id,'pay_status' => 1])->distinct()->pluck('course_id');
                if ($data['is_show'] == true) {
                    $where[] = [function ($query) use ($order_arr) {
                        $query->whereIn('courses.id',$order_arr);
                    }];
                    Log::info('where: ',$where);
                }
            } else {
                $where[] = ['courses.end_time','>=',Carbon::now()];
            }
            if (isset($data['province'])) {
                $id = Region::where('region_name',$data['province'])->value('id');
                $where[] = ['courses.province','=',$id];
            }
            if (isset($data['district'])) {
                $city_id = Region::where('region_name',$data['city'])->value('id');
                $id = Region::where(['region_name' => $data['district'],'parent_id' => $city_id])->value('id');
                $where[] = ['courses.district','=',$id];
            }
            if (isset($data['gender'])) {
                $where[] = ['courses.gender','=',$data['gender']];
            }
            if (isset($data['created_at_start']) && isset($data['created_at_end'])) {
                $where[] = $or_where[] = ['courses.created_at','>=',$data['created_at_start']];
                $where[] = $or_where[] = ['courses.created_at','<=',$data['created_at_end']];
            }
        } else {
            $where[] = ['courses.end_time','>=',Carbon::now()];
        }
        $result = Course::leftJoin('organizations','courses.organ_id','=','organizations.id')
            ->select($select_field)
            ->where($where)
            ->where('courses.is_on',1)
            ->where('courses.status',1)
            ->whereNotIn('courses.is_invalid',[1])
            ->orWhere(function ($query) use ($or_where,$user) {
                $query->where('courses.is_on',1)
                    ->where('courses.status',1)
                    ->where('courses.method','线上')
                    ->whereNotIn('courses.is_invalid',[1])
                    ->where($or_where);
            })
            ->orderBy($sort_field,$order)
            ->logListenedSql()
            ->paginate($page_size);

        foreach ($result as $v) {
            // 是否已报名
            $v->is_entry = UserCourse::where(['user_id' => $user->id,'course_id' => $v->id])->exists();
            /*if ($v->adder_role == 4) {
                $v->distance = calculate_distance($latitude,$longitude,$v->organization->latitude,$v->organization->longitude);
            } else {
                $v->distance = calculate_distance($latitude,$longitude,$v->latitude,$v->longitude);
            }*/
            // 是否已投递
            if ($v->adder_role == 0) {
                $v->is_deliver = DeliverLog::where(['user_id' => $user->id,'course_id' => $v->id,'pay_status' => 1])->exists();
            } else {
                $v->is_deliver = DeliverLog::where(['user_id' => $user->id,'course_id' => $v->id,'pay_status' => 1])->exists();
            }
            // $v->distance = round($v->distance,2);
            if ($v->adder_role == 0) {
                // 是否查看
                $v->is_show = DeliverLog::where(['user_id' => $user->id,'course_id' => $v->id,'pay_status' => 1])->exists();
            }
            if ($v->method !== '线上') {
                $v->province = $v->province_info->region_name;
                $v->city = $v->city_info->region_name;
                $v->district = $v->district_info ? $v->district_info->region_name : null;
            }
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
        $amount = get_service_price(3, $course_info->organization->province_id,$course_info->organization->city_id,$course_info->organization->district_id);
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
        if (in_array($user->role,[1,2])) {
            $course_info->entry_number += 1;
            $course_info->save();
        }
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

    /**
     * 上架/下架
     * @return \Illuminate\Http\JsonResponse
     */
    public function update_on()
    {
        $data = \request()->all();
        $course_id = $data['course_id'] ?? 0;
        $course_info = Course::find($course_id);
        if (!$course_info) {
            return $this->error('课程不存在');
        }
        $message = $course_info->is_on == 0 ? '上架成功' : '下架成功';
        $course_info->is_on = !$course_info->is_on;
        $course_info->update();
        return $this->success($message);
    }
}
