<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\DeliverLog;
use App\Models\Message;
use App\Models\ParentCourse;
use App\Models\ParentStudent;
use App\Models\User;
use App\Models\UserTeacherOrder;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Validator;
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
        $data = \request()->all();
        $rules = [
            'name' => 'required|string',
            'student' => 'required|string',
            'gender' => 'required|numeric',
            'subject' => 'required'
        ];
        $messages = [
            'name.required' => '名称不能为空',
            'name.string' => '名称必须为字符串',
            'student.required' => '学生不能为空',
            'student.string' => '学生必须为字符串',
            'gender.required' => '性别不能为空',
            'gender.numeric' => '性别必须为数字',
            'subject.required' => '科目不能为空'
        ];
        $validator = Validator::make($data,$rules,$messages);
        $id = $data['id'] ?? 0;
        if ($validator->fails()) {
            $errors = $validator->errors();
            return $this->error(implode(',',$errors->all()));
        }
        // 当前用户
        $user = Auth::user();
        $data['parent_id'] = $user->id;
        $data['role'] = 3;
        $data['created_at'] = Carbon::now();
        $data['status'] = 0;
        $data['adder_role'] = 2;
        $data['adder_id'] = $user->id;
        // 处理时间
        $data['class_date'] = json_encode($data['class_date']);
        // 保存数据
        $result = Course::updateOrCreate(['id' => $id],$data);
        if (!$result) {
            return $this->error('发布失败');
        }
        if (!$result->number) {
            $number = create_course_number($result->id);
            $result->number = $number;
            $result->save();
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
        $page_size = $data['page_size'] ?? 10;
        // 当前用户
        $user = Auth::user();
        // $user = User::find(2);
        // 查询数据
        $result = Course::where(['parent_id' => $user->id,'status' => $status,'adder_role' => 2])->orderByDesc('created_at')->paginate($page_size);
        foreach ($result as $v) {
            $v->class_time = json_decode($v->class_date,true);
            $deliver_info =$v->deliver()->where('pay_status',1)->first();
            $v->course_status = $deliver_info->status;
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
            (new Message())->saveMessage($user_id,$user->id,'选中信息','您被选中了',0,2);
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
