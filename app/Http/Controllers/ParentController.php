<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseSetting;
use App\Models\DeliverLog;
use App\Models\Message;
use App\Models\ParentCourse;
use App\Models\ParentStudent;
use App\Models\Region;
use App\Models\SystemMessage;
use App\Models\User;
use App\Models\UserTeacherOrder;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Validator;
use Overtrue\EasySms\EasySms;
use Overtrue\EasySms\Exceptions\Exception;
use Overtrue\EasySms\Exceptions\NoGatewayAvailableException;
use Overtrue\EasySms\PhoneNumber;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\JWT;

class ParentController extends Controller
{
    /**
     * 我的学员
     * @return \Illuminate\Http\JsonResponse
     */
    public function my_students()
    {
        // 当前用户
        $user = Auth::user();
        // 学生
        $student_info = ParentStudent::where('user_id',$user->id)->get();
        return $this->success('我的学员',$student_info);
    }

    /**
     * 发布需求
     * @return \Illuminate\Http\JsonResponse
     */
    public function create_course()
    {
        $config = config('services.sms');
        $data = \request()->all();
        $rules = [
            'name' => 'required',
            'class_commission' => 'required',
            'method' => 'required',
            'introduction' => 'required',
            'valid_time' => 'required',
            'contact' => 'required',
            'mobile' => 'required',
        ];
        $messages = [
            'name.required' => '名称不能为空',
            'class_commission.required' => '课时佣金不能为空',
            'method.required' => '上课方式不能为空',
            'introduction.required' => '详情不能为空',
            'valid_time.required' => '有效期不能为空',
            'mobile.required' => '手机号不能为空',
        ];
        $validator = Validator::make($data,$rules,$messages);
        $id = $data['id'] ?? 0;
        if ($validator->fails()) {
            $errors = $validator->errors();
            return $this->error(implode(',',$errors->all()));
        }
        // 查询招教师需求最大的有效期
        $system_valid_time = CourseSetting::where(['role' => 3])->orderByDesc('created_at')->value('end_time');
        if ($data['valid_time'] > $system_valid_time) {
            return $this->error('有效期不能超过'.$system_valid_time.'天');
        }
        // 当前用户
        $user = Auth::user();
        $data['parent_id'] = $user->id;
        $data['role'] = 3;
        $data['created_at'] = Carbon::now();
        $data['end_time'] = Carbon::now()->setTime(23,59,59)->addDays($data['valid_time']);
        $data['status'] = 0;
        $data['is_on'] = 1;
        // $data['introduction'] = nl2br($data['introduction']);
        $data['adder_role'] = 2;
        $data['adder_id'] = $user->id;
        $data['adder_name'] = $user->name;
        if ($data['method'] !== '线上') {
            $location = get_location($data['longitude'],$data['latitude']);
            $data['province'] = Region::where(['region_name' => $location['province']])->value('id');
            $data['city'] = Region::where(['region_name' => $location['city'],'parent_id' => $data['province']])->value('id');
            $data['district'] = Region::where(['region_name' => $location['district'],'parent_id' => $data['city']])->value('id');
        }
        // 保存数据
        $result = Course::updateOrCreate(['id' => $id],$data);
        if (!$result) {
            return $this->error('发布失败');
        }
        if (!$result->number) {
            $number = new_create_course_number($result->id,$data['method'],2,3);
            $result->number = $number;
            $result->save();
        }
        if (SystemMessage::where('action',7)->value('text_message') == 1) {
            $admin_mobile = SystemMessage::where('action',7)->value('admin_mobile');
            // 发送短信
            $easySms = new EasySms($config);
            try {
                $admin_number = new PhoneNumber($admin_mobile);
                $easySms->send($admin_number,[
                    'content'  => "【添添学】有新发布的需求",
                ]);
            } catch (Exception|NoGatewayAvailableException $exception) {
                return $this->error($exception->getResults());
            }
        }
        return $this->success('操作成功');
    }

    /**
     * 我的发布
     * @return \Illuminate\Http\JsonResponse
     */
    public function course_list()
    {
        $data = \request()->all();
        $status = $data['status'] ?? 0;
        Log::info('status: '.$status);
        $page_size = $data['page_size'] ?? 10;
        // 当前用户
        $user = Auth::user();
        // $user = User::find(11);
        // 查询数据
        $result = Course::where(['adder_id' => $user->id,'status' => $status,'adder_role' => 2])->orderByDesc('created_at')->paginate($page_size);
        foreach ($result as $v) {
            $v->class_time = json_decode($v->class_date,true);
            /*if ($v->status == 1) {
                $deliver_info = $v->deliver()->first();
                if ($deliver_info) {
                    $v->course_status = $deliver_info->status;
                }
            }*/
        }
        return $this->success('我的发布',$result);
    }

    /**
     * 课程详情
     * @return \Illuminate\Http\JsonResponse
     */
    public function course_detail()
    {
        $data = \request()->all();
        $course_id = $data['course_id'] ?? 0;
        $course_info = Course::find($course_id);
        if (!$course_info) {
            return $this->error('课程不存在');
        }
        $course_info->class_time = json_decode($course_info->class_date,true);
        $course_info->class_address = $course_info->province_info->region_name.$course_info->city_info->region_name.$course_info->district_info->region_name.$course_info->address;
        // 已投递教师人数
        $course_info->delivery_count = DeliverLog::where(['course_id' => $course_id])->count();
        return $this->success('课程详情',$course_info);
    }

    /**
     * 选中教师
     * @return \Illuminate\Http\JsonResponse
     */
    public function check_teacher()
    {
        $config = config('services.sms');
        $data = \request()->all();
        $deliver_arr = $data['id'] ?? [];
        $result = DeliverLog::whereIn('id',$deliver_arr)->update(['is_checked' => 1,'status' => 1]);
        if (!$result) {
            return $this->error('操作失败');
        }
        // 当前用户
        $user = Auth::user();
        // 给选中的教师发送提醒
        foreach ($deliver_arr as $v) {
            $user_id = DeliverLog::where('id',$v)->value('user_id');
            $course_id = DeliverLog::where('id',$v)->value('course_id');
            // 发送通知
            if (SystemMessage::where('action',10)->value('site_message') == 1) {
                (new Message())->saveMessage($user_id,$user->id,'选中信息','您被选中了',$course_id,0,2);
            }
            if (SystemMessage::where('action',10)->value('text_message') == 1) {
                $text = '投递';
                // 发送短信
                $easySms = new EasySms($config);
                $mobile = User::where('id',$user_id)->value('mobile');
                try {
                    $number = new PhoneNumber($mobile);
                    $easySms->send($number,[
                        'content'  => "【添添学】恭喜您，您的".$text."被选中",
                    ]);
                } catch (Exception|NoGatewayAvailableException $exception) {
                    return $this->error($exception->getResults());
                }
            }
        }
        return $this->success('操作成功');
    }

    /**
     * 是否能切换身份
     * @return \Illuminate\Http\JsonResponse
     */
    public function can_change_role()
    {
        // 当前用户
        $user = Auth::user();
        if (!in_array($user->role,[1,2])) {
            return $this->error('您不能切换身份');
        }
        // 家长有多个学生时不能切换
        if ($user->role == 2 && $user->student->count() > 1) {
            return $this->error('您不能切换身份');
        }
        return $this->success('可以切换');
    }

    /**
     * 切换角色
     * @return \Illuminate\Http\JsonResponse
     */
    public function change_role()
    {
        // 当前用户
        $user = Auth::user();
        if (!in_array($user->role,[1,2])) {
            return $this->error('您不能切换身份');
        }
        Log::info('count: '.$user->student->count() > 1);
        // 家长有多个学生时不能切换
        if ($user->role == 2 && $user->student->count() > 1) {
            return $this->error('您不能切换身份');
        }
        // 判读当前用户是否存在其他账号
        $account = User::where('number',$user->number)->get();
        $role = $user->role == 1 ? 2 : 1;
        $is_new = 1;
        if (count($account) > 1) {
            // 查询另外一个账号id
            $other_id = $account->pluck('id')->reject(function ($item) use ($user) {
                return $item == $user->id;
            })->pop();
            $other_user = User::find($other_id);
            // 用户登录
            $token = JWTAuth::fromUser($other_user);
            //设置token
            Redis::set('TOKEN:'.$other_id,$token);
            if ($other_user->mobile) {
                $is_new = 0;
            }
            return $this->success('切换成功',compact('token','is_new','role'));
        }
        // 不存在其他账号，重新注册
        $new_user = new User();
        $new_user->role = $role;
        $new_user->number = $user->number;
        $new_user->open_id = $user->open_id;
        $new_user->save();
        $token = JWTAuth::fromUser($new_user);
        // 设置token
        Redis::set('TOKEN:'.$new_user->id,$token);
        return $this->success('切换成功',compact('token','is_new','role'));
    }

    /**
     * 获取被选中的教师
     * @return \Illuminate\Http\JsonResponse
     */
    public function deliver_teachers()
    {
        $data = \request()->all();
        $course_id = $data['course_id'] ?? 0;
        $page_size = $data['page_size'] ?? 10;
        // 投递列表
        $result = DeliverLog::with(['user'])->where(['course_id' => $course_id,'is_checked' => 1,'pay_status' => 1])->paginate($page_size);
        foreach ($result as $v) {
            $v->teacher_info = $v->user->teacher_info;
            $v->subject = $v->course->subject;
            $v->teacher_tags = $v->user->teacher_tags->pluck('tag');
        }
        return $this->success('投递教师列表',$result);
    }
}
