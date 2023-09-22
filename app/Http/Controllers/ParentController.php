<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\ParentCourse;
use App\Models\ParentStudent;
use App\Models\UserTeacherOrder;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

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
        $data['created_at'] = Carbon::now();
        $data['status'] = 0;
        $data['adder_role'] = 3;
        // 处理时间
        if ($data['class_type'] == 2) {
            $data['class_date'] = json_encode($data['class_date']);
        }
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
        // 查询数据
        $result = ParentCourse::where(['user_id' => $user->id,'status' => $status])->orderByDesc('created_at')->paginate($page_size);
        foreach ($result as $v) {
            if ($v->class_type == 2) {
                $v->class_time = json_decode($v->class_time,true);
            }
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
        $course_info = ParentCourse::with(['reviewer' => function ($query) {
            return $query->select('id','name');
        }])->find($course_id);
        if (!$course_info) {
            return $this->error('课程不存在');
        }
        if ($course_info->class_type == 2) {
            $course_info->class_time = json_decode($course_info->class_time,true);
        }
        return $this->success('课程详情',$course_info);
    }
}
