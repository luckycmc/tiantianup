<?php

namespace App\Http\Controllers;

use App\Models\ParentStudent;
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
        $rules = [];
        $messages = [];
        $validator = Validator::make($data,$rules,$messages);
        if ($validator->fails()) {
            $errors = $validator->errors();
            return $this->error(implode(',',$errors->all()));
        }
        // 当前用户
        $user = Auth::user();
        $data['user_id'] = $user->id;
        $data['created_at'] = Carbon::now();
        $data['status'] = 0;
        // 处理时间
        if ($data['class_type'] == 2) {
            $data['class_time'] = json_encode($data['class_time']);
        }
        // 保存数据
        $result = DB::table('parent_courses')->insert($data);
        if (!$result) {
            return $this->error('发布失败');
        }
        return $this->success('发布成功');
    }

    public function course_list()
    {
        
    }
}
