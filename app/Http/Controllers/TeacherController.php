<?php

namespace App\Http\Controllers;

use App\Models\BaseInformation;
use App\Models\Course;
use App\Models\DeliverLog;
use App\Models\PlatformMessage;
use App\Models\Region;
use App\Models\SystemMessage;
use App\Models\TeacherCert;
use App\Models\TeacherCourseOrder;
use App\Models\TeacherEducation;
use App\Models\TeacherImage;
use App\Models\TeacherInfo;
use App\Models\TeacherRealAuth;
use App\Models\TeacherTag;
use App\Models\User;
use App\Models\UserTeacherOrder;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Overtrue\EasySms\EasySms;
use Overtrue\EasySms\Exceptions\Exception;
use Overtrue\EasySms\Exceptions\NoGatewayAvailableException;
use Overtrue\EasySms\PhoneNumber;

class TeacherController extends Controller
{
    /**
     * 教师列表
     * @return \Illuminate\Http\JsonResponse
     */
    public function list()
    {
        $data = \request()->all();
        $district_id = $data['district_id'] ?? 0;
        $where = [];
        if (isset($data['longitude']) && isset($data['latitude']) && !isset($data['district_id'])) {
            // 根据经纬度获取省市区
            $location = get_location($data['longitude'],$data['latitude']);
            if (!$location) {
                return $this->error('定位出错');
            }
            $district_id = Region::where('code',$location['adcode'])->value('id');
            $where[] = ['users.district_id','=',$district_id];
        }
        // 当前用户
        $user = Auth::user();
        // $user = User::find(18);
        $page_size = $data['page_size'] ?? 10;
        $page = $data['page'] ?? 1;
        // 排序
        $order = $data['order'] ?? 'desc';
        $sort_field = 'users.age';
        if (isset($data['sort_teaching_year'])) {
            $sort_field = 'teacher_info.teaching_year';
        } elseif (isset($data['sort_education'])) {
            $sort_field = 'teacher_education.education_id';
        }
        // 筛选

        if (isset($data['city_id'])) {
            $where[] = ['users.city_id','=',$data['city_id']];
        }
        if (isset($data['city'])) {
            $city_id = Region::where('region_name',$data['city'])->value('id');
            $where[] = ['users.city_id','=',$city_id];
        }
        if (isset($data['district_id'])) {
            $where[] = ['users.district_id','=',$data['district_id']];
        }
        if (isset($data['filter_object'])) {
            $where[] = ['teacher_career.object','like','%'.$data['filter_object'].'%'];
        }
        if (isset($data['filter_subject'])) {
            $where[] = ['teacher_career.subject','like','%'.$data['filter_subject'].'%'];
        }
        if (isset($data['filter_gender'])) {
            $where[] = ['users.gender','=',$data['filter_gender']];
        }
        if (isset($data['filter_education'])) {
            $where[] = ['teacher_education.highest_education','=',$data['filter_education']];
        }
        if (isset($data['filter_teaching_year_min']) && isset($data['filter_teaching_year_max'])) {
            $where[] = ['teacher_info.teaching_year','>',$data['filter_teaching_year_min']];
            $where[] = ['teacher_info.teaching_year','<',$data['filter_teaching_year_max']];
        }
        if (isset($data['filter_is_auth'])) {
            $where[] = ['users.is_real_auth','=',$data['is_real_auth']];
        }
        $result = User::leftJoin('teacher_info', 'users.id', '=', 'teacher_info.user_id')
            ->leftJoin('teacher_education','users.id','=','teacher_education.user_id')
            ->leftJoin('teacher_career','users.id','=','teacher_career.user_id')
            ->where($where)
            ->where(['users.role' => 3])
            ->select('users.*','teacher_education.highest_education','teacher_education.graduate_school','users.teaching_year as teacher_info.teaching_year','teacher_career.subject','teacher_info.picture')
            ->orderBy($sort_field,$order)
            ->distinct()
            ->paginate($page_size);
        // dd($result->toArray());
        /*// 去重
        $info = $result->unique('id')->values();
        // 分页
        $result = new \Illuminate\Pagination\LengthAwarePaginator(
            $info,
            count($info),
            $page_size,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );*/
        foreach ($result as $v) {
            // 科目
            $v->subject = explode(',',$v->subject);
            $v->is_pay = UserTeacherOrder::where(['user_id' => $user->id,'teacher_id' => $v->id,'status' => 1])->exists();
        }
        return $this->success('教师列表',$result);
    }

    /**
     * 证书
     * @return \Illuminate\Http\JsonResponse
     */
    public function update_cert()
    {
        $config = config('services.sms');
        $data = \request()->all();
        $rules = [
            'teacher_cert' => 'required',
            'other_cert' => 'required',
            'honor_cert' => 'required'
        ];
        $messages = [
            'teacher_cert.required' => '教师资格证不能为空',
            'other_cert.required' => '其他证书不能为空',
            'honor_cert.required' => '荣誉证书不能为空'
        ];
        $validator = Validator::make($data,$rules,$messages);
        if ($validator->fails()) {
            $errors = $validator->errors();
            return $this->error(implode(',',$errors->all()));
        }

        // 当前用户
        $user = Auth::user();
        $education_data = [
            'user_id' => $user->id,
            'teacher_cert' => is_array($data['teacher_cert']) ? json_encode($data['teacher_cert']) : json_encode([$data['teacher_cert']]),
            'other_cert' => is_array($data['other_cert']) ? json_encode($data['other_cert']) : json_encode([$data['other_cert']]),
            'honor_cert' => is_array($data['honor_cert']) ? json_encode($data['honor_cert']) : json_encode([$data['honor_cert']]),
            'status' => 0
        ];
        $result = TeacherCert::updateOrCreate(['user_id' => $user->id],$education_data);
        if (!$result) {
            return $this->error('提交失败');
        }
        // 发送通知
        if (SystemMessage::where('action',3)->value('site_message') == 1) {
            (new PlatformMessage())->saveMessage('教师证书更新','教师证书更新','教师端');
        }
        if (SystemMessage::where('action',3)->value('text_message') == 1) {
            $admin_mobile = SystemMessage::where('action',3)->value('admin_mobile');
            // 发送短信
            $easySms = new EasySms($config);
            try {
                $admin_number = new PhoneNumber($admin_mobile);
                $text = '教师证书';
                $easySms->send($admin_number,[
                    'content'  => "【添添学】".$text."更新",
                ]);
            } catch (Exception|NoGatewayAvailableException $exception) {
                return $this->error($exception->getResults());
            }
        }
        return $this->success('提交成功');
    }

    /**
     * 实名认证&教育经历&资格证书&教师风采
     * @return \Illuminate\Http\JsonResponse
     */
    public function get_info()
    {
        $data = \request()->all();
        $type = $data['type'] ?? 1;
        // 当前用户
        $user = Auth::user();
        $arr = [new TeacherInfo(),new TeacherEducation(),new TeacherCert(),new TeacherImage()];
        // 查询实名认证
        $result = $arr[$type-1]::where('user_id',$user->id)->first();
        if ($type == 3) {
            if ($result) {
                $result->teacher_cert = isset($result->teacher_cert) ? json_decode($result->teacher_cert,true) : null;
                $result->other_cert = json_decode($result->other_cert,true);
                $result->honor_cert = json_decode($result->honor_cert,true);
            }
        }
        if ($type == 4) {
            if ($result) {
                $result->url = json_decode($result->url,true);
            }
        }
        return $this->success('教师信息',$result);
    }

    /**
     * 更新教师风采
     * @return \Illuminate\Http\JsonResponse
     */
    public function update_teacher_images()
    {
        $config = config('services.sms');
        $data = \request()->all();
        $rules = [
            'url' => 'required',
        ];
        $messages = [
            'url.required' => '教师风采不能为空',
        ];
        $validator = Validator::make($data,$rules,$messages);
        if ($validator->fails()) {
            $errors = $validator->errors();
            return $this->error(implode(',',$errors->all()));
        }

        // 当前用户
        $user = Auth::user();
        $insert_data = [
            'user_id' => $user->id,
            'url' => is_array($data['url']) ? json_encode($data['url']) : json_encode([$data['url']]),
            'type' => 2,
            'status' => 0
        ];
        $result = TeacherImage::updateOrCreate(['user_id' => $user->id],$insert_data);
        if (!$result) {
            return $this->error('提交失败');
        }
        // 发送通知
        if (SystemMessage::where('action',3)->value('site_message') == 1) {
            (new PlatformMessage())->saveMessage('教师风采更新','教师风采更新','教师端');
        }
        if (SystemMessage::where('action',3)->value('text_message') == 1) {
            $admin_mobile = SystemMessage::where('action',3)->value('admin_mobile');
            // 发送短信
            $easySms = new EasySms($config);
            try {
                $admin_number = new PhoneNumber($admin_mobile);
                $text = '教师风采';
                $easySms->send($admin_number,[
                    'content'  => "【添添学】".$text."更新",
                ]);
            } catch (Exception|NoGatewayAvailableException $exception) {
                return $this->error($exception->getResults());
            }
        }
        return $this->success('提交成功');
    }

    /**
     * 我的接单
     * @return \Illuminate\Http\JsonResponse
     */
    public function my_courses()
    {
        $data = \request()->all();
        $page_size = $data['page_size'] ?? 10;
        $page = $data['page'] ?? 10;
        $longitude = $data['longitude'] ?? 0;
        $latitude = $data['latitude'] ?? 0;
        // 当前用户
        $user = Auth::user();
        $where = [];
        if (isset($data['status'])) {
            $where[] = ['status','=',$data['status']];
        }
        if (isset($data['search_keyword'])) {
            $where[] = ['name','like','%'.$data['search_keyword'].'%'];
        }
        $result = DeliverLog::with('course')->where('user_id',$user->id)->where($where)->get();
        foreach ($result as $v) {
            $v->course->distance = calculate_distance($latitude,$longitude,floatval($v->course->latitude),floatval($v->course->longitude));
            // 机构
            if ($v->course->adder_role == 4) {
                // $v->course->distance = calculate_distance($latitude,$longitude,$v->course->organization->latitude,$v->course->organization->longitude);
                $v->course->course_role = $v->course->adder_role;
                $v->organization_name = $v->course->organization->name;
            }
            $v->course->pay_status = $v->pay_status;
            $v->course->is_checked = $v->is_checked;
        }
        $course = $result->map(function ($item) {
            return $item->course;
        });
        $course = $course->filter(function ($item) {
            return $item->adder_role !== 0;
        })->values();
        if (isset($data['name'])) {
            $course = $course->filter(function ($user) use ($data) {
                return str_contains(strtolower($user['name']), strtolower($data['name']));
            });
        }
        /*if (isset($data['search_keyword'])) {
            $course = $course->filter(function ($course) use ($data) {
                return str_contains(strtolower($course['name']), strtolower($data['search_keyword']));
            });
        }*/
        // 分页
        $result = new LengthAwarePaginator(
            $course->forPage($page, $page_size),
            $course->count(),
            $page_size,
            $page,
            ['path' => LengthAwarePaginator::resolveCurrentPath()]
        );
        return $this->success('我的接单',$result);
    }

    /**
     * 招学员列表
     * @return \Illuminate\Http\JsonResponse
     */
    public function course_list()
    {
        $data = \request()->all();
        $page_size = $data['page_size'] ?? 10;
        $longitude = $data['longitude'] ?? 0;
        $latitude = $data['latitude'] ?? 0;

        // 当前用户
        $user = Auth::user();
        // $user = User::find(10);
        // $user = User::find(4);
        $sort_field = 'courses.created_at';
        $order = 'desc';
        if (isset($data['sort_created_at'])) {
            $order = $data['sort_created_at'] == 0 ? 'desc' : 'asc';
        }
        if (isset($data['sort_class_price'])) {
            $sort_field = 'courses.class_price';
            $order = $data['sort_class_price'] == 0 ? 'desc' : 'asc';
        }
        if (isset($data['sort_distance'])) {
            $sort_field = 'distance';
            $order = $data['sort_distance'] == 0 ? 'desc' : 'asc';
        }
        $where = [];
        $or_where = [];
        // $city_id = 0;
        if (isset($data['city_id'])) {
            $city_id = $data['city_id'];
            $where[] = ['courses.city','=',$city_id];
        } else if (isset($data['city'])) {
            $city_id = Region::where('region_name',$data['city'])->value('id');
            $where[] = ['courses.city','=',$city_id];
        } else {
            // 当前城市
            $location_info = get_location($longitude,$latitude);
            $city = $location_info['city'];
            $city_id = Region::where('region_name',$city)->value('id');
            $where[] = ['courses.city','=',$city_id];
        }

        if (isset($data['filter_type'])) {
            $where[] = ['courses.type','=',$data['filter_type']];
        }
        if (isset($data['filter_method'])) {
            $where[] = $or_where[] = ['courses.method','=',$data['filter_method']];
        }
        if (isset($data['filter_subject'])) {
            $where[] = ['courses.subject','=',$data['filter_subject']];
        }
        if (isset($data['filter_grade'])) {
            $where[] = ['courses.grade','=',$data['filter_grade']];
        }
        if (isset($data['filter_adder_role'])) {
            $where[] = ['courses.adder_role','=',$data['filter_adder_role']];
        }
        if (isset($data['name'])) {
            $where[] = $or_where[] = ['courses.name','like','%'.$data['name'].'%'];
        }
        if (isset($data['district'])) {
            $region_info = get_long_lat('','',$data['district'],'');
            Log::info('city_id: '.$city_id);
            $district_id = Region::where('region_name',$data['district'])->where('parent_id',$city_id)->value('id');
            $where[] = ['courses.district','=',$district_id];
            /*$longitude = $region_info[0];
            $latitude = $region_info[1];*/
        }
        if (isset($data['filter_class_price_min']) && isset($data['filter_class_price_max'])) {
            $where[] = $or_where[] = ['courses.class_price','>=',$data['filter_class_price_min']];
            $where[] = $or_where[] = ['courses.class_price','<=',$data['filter_class_price_max']];
        }
        if (isset($data['filter_distance_min']) && isset($data['filter_distance_max'])) {
            $distance_expr = "6371 * acos(cos(radians($latitude)) * cos(radians(courses.latitude)) * cos(radians(courses.longitude) - radians($longitude)) + sin(radians($latitude)) * sin(radians(courses.latitude)))";
            $where[] = [DB::raw($distance_expr),'>=',$data['filter_distance_min']];
            $where[] = [DB::raw($distance_expr),'<=',$data['filter_distance_max']];
        }
        Log::info('where: ',$where);
        if (isset($data['filter_delivery_status'])) {
            $delivery_arr = DeliverLog::where('user_id',$user->id)->pluck('course_id');
            if ($data['filter_delivery_status'] == 0) {
                // 未投递
                $condition = "whereNotIn";
            } else {
                // 已投递
                $condition = "whereIn";
                $where[] = $or_where[] = ['deliver_log.status','=',$data['filter_delivery_status']];
            }
            $result = Course::leftJoin('organizations','organizations.id','=','courses.organ_id')
                ->leftJoin('deliver_log','deliver_log.course_id','=','courses.id')
                ->select('courses.*','organizations.name as organ_name',DB::raw('6371 * ACOS(COS(RADIANS('.$latitude.')) * COS(RADIANS(courses.latitude)) * COS(RADIANS(courses.longitude) - RADIANS('.$longitude.')) + SIN(RADIANS('.$latitude.')) * SIN(RADIANS(courses.latitude))) AS distance'))
                ->where($where)->where(['courses.role' => 3,'courses.status' => 1])->where('courses.is_on',1)->where('courses.adder_role','!=',0)->orWhere(function ($query) use ($or_where) {
                    $query->where('courses.is_on',1)
                        ->where('courses.status','!=',0)
                        ->where('courses.method','线上')
                        ->where('courses.role',3)
                        ->where('courses.adder_role','!=',0)
                        ->where($or_where);
                })->$condition('courses.id',$delivery_arr)->orderBy($sort_field,$order)->distinct()->paginate($page_size);
        } else {
            $result = Course::leftJoin('organizations','organizations.id','=','courses.organ_id')
                ->leftJoin('deliver_log','deliver_log.course_id','=','courses.id')
                ->select('courses.*','organizations.name as organ_name',DB::raw('6371 * ACOS(COS(RADIANS('.$latitude.')) * COS(RADIANS(courses.latitude)) * COS(RADIANS(courses.longitude) - RADIANS('.$longitude.')) + SIN(RADIANS('.$latitude.')) * SIN(RADIANS(courses.latitude))) AS distance'))
                ->where($where)->where(['courses.role' => 3])->where('courses.is_on',1)->where('courses.adder_role','!=',0)->orWhere(function ($query) use ($or_where) {
                    $query->where('courses.is_on',1)
                        ->where('courses.status','!=',0)
                        ->where('courses.method','线上')
                        ->where('courses.role',3)
                        ->where('courses.adder_role','!=',0)
                        ->where($or_where);
                })->orderBy($sort_field,$order)->distinct()->paginate($page_size);
        }

        foreach ($result as $v) {
            $v->distance = round(calculate_distance($latitude,$longitude,$v->latitude,$v->longitude),2);
            $v->is_deliver = $user->deliver_log()->where('course_id',$v->id)->exists();
        }
        return $this->success('找学员列表',$result);
    }

    /**
     * 投递详情
     * @return \Illuminate\Http\JsonResponse
     */
    public function deliver_detail()
    {
        $data = \request()->all();
        $course_id = $data['course_id'] ?? 0;
        // 当前教师
        $user = Auth::user();
        if (!Course::find($course_id)) {
            return $this->error('课程不存在');
        }
        $result = DeliverLog::with('course')->where(['user_id' => $user->id,'course_id' => $course_id])->first();
        if ($result->pay_status == 1) {
            $result->mobile = $result->course->adder->mobile;
        }
        return $this->success('投递详情',$result);
    }

    /**
     * 创建课程订单
     * @return \Illuminate\Http\JsonResponse
     */
    public function create_course_order()
    {
        $data = \request()->all();
        $course_id = $data['course_id'] ?? 0;
        $course_info = Course::find($course_id);
        if (!$course_info) {
            return $this->error('需求不存在');
        }
        /*if ($course_info->course_status == 4) {
            return $this->error('该课程被抢走啦');
        }*/
        // 当前用户
        $user = Auth::user();
        $out_trade_no = app('snowflake')->id();
        if ($course_info->adder_role == 0) {
            $type = 4;
        } else {
            $type = 3;
        }
        $amount = get_service_price($type,$user->province_id,$user->city_id,$user->district_id);
        $insert_data = [
            'user_id' => $user->id,
            'course_id' => $course_id,
            'out_trade_no' => $out_trade_no,
            // 'amount' => BaseInformation::value('service_price'),
            'amount' => $amount,
            'pay_status' => 0,
            'created_at' => Carbon::now()
        ];
        $result = DeliverLog::updateOrCreate(['out_trade_no' => $out_trade_no],$insert_data);
        if (!$result) {
            return $this->error('操作失败');
        }
        return $this->success('创建成功',compact('out_trade_no','amount'));
    }

    /**
     * 更新标签显示状态
     * @return \Illuminate\Http\JsonResponse
     */
    public function update_tag_status()
    {
        $data = \request()->all();
        $id = $data['id'] ?? 0;
        $tag_info = TeacherTag::find($id);
        if (!$tag_info) {
            return $this->error('标签不存在');
        }
        $tag_info->is_show = !$tag_info->is_show;
        $tag_info->update();
        return $this->success('操作成功');
    }

    /**
     * 删除标签
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete_tag()
    {
        $data = \request()->all();
        $id = $data['id'] ?? 0;
        $tag_info = TeacherTag::find($id);
        if (!$tag_info) {
            return $this->error('标签不存在');
        }
        $tag_info->delete();
        return $this->success('操作成功');
    }
}
