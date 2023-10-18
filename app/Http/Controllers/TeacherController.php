<?php

namespace App\Http\Controllers;

use App\Models\BaseInformation;
use App\Models\Course;
use App\Models\DeliverLog;
use App\Models\Region;
use App\Models\TeacherCert;
use App\Models\TeacherCourseOrder;
use App\Models\TeacherEducation;
use App\Models\TeacherImage;
use App\Models\TeacherInfo;
use App\Models\TeacherRealAuth;
use App\Models\User;
use App\Models\UserTeacherOrder;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

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
        if (isset($data['longitude']) && isset($data['latitude'])) {
            // 根据经纬度获取省市区
            $location = get_location($data['longitude'],$data['latitude']);
            if (!$location) {
                return $this->error('定位出错');
            }
            $district_id = Region::where('code',$location['adcode'])->value('id');
        }
        // 当前用户
        $user = Auth::user();
        $page_size = $data['page_size'] ?? 10;
        // 排序
        $order = $data['order'] ?? 'desc';
        $sort_field = 'users.age';
        if (isset($data['sort_teaching_year'])) {
            $sort_field = 'teacher_info.teaching_year';
        } elseif (isset($data['sort_education'])) {
            $sort_field = 'teacher_education.education_id';
        }
        // 筛选
        $where = [];
        if (isset($data['city_id'])) {
            $where[] = ['users.city_id','=',$data['city_id']];
        }
        if (isset($data['filter_object'])) {
            $where[] = ['teacher_career.object','like','%'.$data['filter_object'].'%'];
        }
        if (isset($data['filter_subject'])) {
            $where[] = ['teacher_career.object','like','%'.$data['filter_subject'].'%'];
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
            ->where(['users.district_id' => $district_id,'users.role' => 3])
            ->orderBy($sort_field,$order)
            ->select('users.*','teacher_education.highest_education','teacher_education.graduate_school','teacher_info.teaching_year','teacher_career.subject')
            ->paginate($page_size);
        foreach ($result as $v) {
            // 科目
            $v->subject = explode(',',$v->subject);
            if ($user->role == 2) {
                $v->is_pay = $v->deliver_log()->where('pay_status',1)->exists();
            } else {
                $v->is_pay = UserTeacherOrder::where(['user_id' => $user->id,'teacher_id' => $v->id,'status' => 1])->exists();
            }
        }
        return $this->success('教师列表',$result);
    }

    /**
     * 证书
     * @return \Illuminate\Http\JsonResponse
     */
    public function update_cert()
    {
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
            $result->teacher_cert = json_decode($result->teacher_cert,true);
            $result->other_cert = json_decode($result->other_cert,true);
            $result->honor_cert = json_decode($result->honor_cert,true);
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
        $longitude = $data['longitude'] ?? 0;
        $latitude = $data['latitude'] ?? 0;
        // 当前用户
        $user = Auth::user();
        $where = [];
        if (isset($data['status'])) {
            $where[] = ['status','=',$data['status']];
        }
        $result = DeliverLog::with('course')->where('user_id',$user->id)->where($where)->paginate($page_size);
        foreach ($result as $v) {
            // 机构
            if ($v->course->adder_role == 4) {
                $v->course->distance = calculate_distance($latitude,$longitude,$v->course->organization->latitude,$v->course->organization->longitude);
                $v->course->course_role = $v->course->adder_role;
            }
            $v->course->pay_status = $v->pay_status;
        }
        $course = $result->map(function ($item) {
            return $item->course;
        });
        if (isset($data['name'])) {
            $course = $course->filter(function ($user) use ($data) {
                return str_contains(strtolower($user['name']), strtolower($data['name']));
            });
        }
        return $this->success('我的接单',$course);
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
        $sort_field = 'courses.created_at';
        $order = 'desc';
        if (isset($data['sort_class_price'])) {
            $sort_field = 'courses.class_price';
            $order = $data['sort_class_price'] == 0 ? 'desc' : 'asc';
        }
        if (isset($data['sort_distance'])) {
            $sort_field = 'distance';
            $order = $data['sort_distance'] == 0 ? 'desc' : 'asc';
        }
        $where = [];
        if (isset($data['filter_type'])) {
            $where[] = ['courses.type','=',$data['filter_type']];
        }
        if (isset($data['filter_method'])) {
            $where[] = ['courses.method','=',$data['filter_method']];
        }
        if (isset($data['subject'])) {
            $where[] = ['courses.subject','=',$data['subject']];
        }
        if (isset($data['grade'])) {
            $where[] = ['courses.grade','=',$data['grade']];
        }
        if (isset($data['filter_adder_role'])) {
            $where[] = ['courses.adder_role','=',$data['filter_adder_role']];
        }
        if (isset($data['district'])) {
            $where[] = ['courses.district','=',$data['district']];
        }
        if (isset($data['filter_class_price_min']) && isset($data['filter_class_price_max'])) {
            $where[] = ['courses.class_price','>=',$data['filter_class_price_min']];
            $where[] = ['courses.class_price','<=',$data['filter_class_price_max']];
        }
        if (isset($data['filter_distance_min']) && isset($data['filter_distance_max'])) {
            $distance_expr = "6371 * acos(cos(radians($latitude)) * cos(radians(latitude)) * cos(radians(longitude) - radians($longitude)) + sin(radians($latitude)) * sin(radians(latitude)))";
            $where[] = [DB::raw($distance_expr),'>=',$data['filter_distance_min']];
            $where[] = [DB::raw($distance_expr),'<=',$data['filter_distance_max']];
        }
        if (isset($data['filter_delivery_status'])) {
            $delivery_arr = DeliverLog::where('user_id',$user->id)->pluck('course_id');
            if ($data['filter_delivery_status'] == 0) {
                // 未投递
                $condition = "whereNotIn";
            } else {
                // 已投递
                $condition = "whereIn";
                $where[] = ['deliver_log.status','=',$data['filter_delivery_status']];
            }
            $result = Course::leftJoin('organizations','organizations.id','=','courses.organ_id')
                ->leftJoin('deliver_log','deliver_log.course_id','=','courses.id')
                ->select('courses.*','organizations.name as organ_name','organizations.longitude','organizations.latitude',DB::raw('6371 * ACOS(COS(RADIANS('.$latitude.')) * COS(RADIANS(latitude)) * COS(RADIANS(longitude) - RADIANS('.$longitude.')) + SIN(RADIANS('.$latitude.')) * SIN(RADIANS(latitude))) AS distance'))
                ->where($where)->where('courses.role',3)->where('courses.adder_role','!=',0)->$condition('courses.id',$delivery_arr)->orderBy($sort_field,$order)->distinct()->paginate($page_size);
        } else {
            $result = Course::leftJoin('organizations','organizations.id','=','courses.organ_id')
                ->leftJoin('deliver_log','deliver_log.course_id','=','courses.id')
                ->select('courses.*','organizations.name as organ_name','organizations.longitude','organizations.latitude',DB::raw('6371 * ACOS(COS(RADIANS('.$latitude.')) * COS(RADIANS(latitude)) * COS(RADIANS(longitude) - RADIANS('.$longitude.')) + SIN(RADIANS('.$latitude.')) * SIN(RADIANS(latitude))) AS distance'))
                ->where($where)->where('courses.role',3)->where('courses.adder_role','!=',0)->orderBy($sort_field,$order)->distinct()->paginate($page_size);
        }

        foreach ($result as $v) {
            if ($v->adder_role == 4) {
                $v->distance = calculate_distance($latitude,$longitude,$v->latitude,$v->longitude);
            }
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
        if (!Course::find($course_id)) {
            return $this->error('需求不存在');
        }
        // 当前用户
        $user = Auth::user();
        $out_trade_no = app('snowflake')->id();
        $insert_data = [
            'user_id' => $user->id,
            'course_id' => $course_id,
            'out_trade_no' => $out_trade_no,
            // 'amount' => BaseInformation::value('service_price'),
            'amount' => 0.01,
            'pay_status' => 0,
            'created_at' => Carbon::now()
        ];
        $result = DeliverLog::updateOrCreate(['out_trade_no' => $out_trade_no],$insert_data);
        if (!$result) {
            return $this->error('操作失败');
        }
        return $this->success('创建成功',$out_trade_no);
    }
}
