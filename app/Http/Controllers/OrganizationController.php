<?php

namespace App\Http\Controllers;

use App\Models\BaseInformation;
use App\Models\Course;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class OrganizationController extends Controller
{
    /**
     * 填写信息
     * @return \Illuminate\Http\JsonResponse
     */
    public function create()
    {
        $data = \request()->except('images');
        $rules = [];
        $messages = [];
        $validator = Validator::make($data,$rules,$messages);
        if ($validator->fails()) {
            $error = $validator->errors();
            return $this->error(implode(',',$error->all()));
        }
        // 当前用户
        $user = Auth::user();
        // 存入机构
        $data['created_at'] = Carbon::now();
        $data['user_id'] = $user->id;
        $id = DB::table('organizations')->insertGetId($data);
        $images = \request()->input('images');
        if ($images) {
            $image_data = [];
            foreach ($images as $v) {
                $image_data[] = [
                    'organ_id' => $id,
                    'url' => $v,
                    'created_at' => Carbon::now()
                ];
            }
            // 保存图片
            DB::table('organ_images')->insert($image_data);
        }
        return $this->success('提交成功');
    }

    /**
     * 生成订单
     * @return \Illuminate\Http\JsonResponse
     */
    public function create_teacher_order()
    {
        $data = \request()->all();
        $teacher_id = $data['teacher_id'] ?? 0;
        // 查询教师
        $teacher = User::where(['role' => 3,'id' => $teacher_id])->first();
        if (!$teacher) {
            return $this->error('教师不存在');
        }
        // 当前用户
        $user = Auth::user();
        $out_trade_no = app('snowflake')->id();
        // 查询服务费
        $service_price = BaseInformation::value('service_price');
        $order_data = [
            'user_id' => $user->id,
            'role' => 4,
            'teacher_id' => $teacher_id,
            'out_trade_no' => $out_trade_no,
            'amount' => $service_price,
            'discount' => 0,
            'status' => 0,
            'created_at' => Carbon::now()
        ];
        // 保存数据
        $result = DB::table('user_teacher_orders')->insert($order_data);
        if (!$result) {
            return $this->error('生成订单失败');
        }
        return $this->success('生成订单成功',compact('out_trade_no'));
    }

    /**
     * 发布需求
     * @return \Illuminate\Http\JsonResponse
     */
    public function create_course()
    {
        $data = \request()->all();
        $rules = [
            'name' => 'required',
            'type' => 'required',
            'method' => 'required',
            'subject' => 'required',
        ];
        $messages = [
            'name.required' => '名称不能为空',
            'type.required' => '辅导类型不能为空',
            'method.required' => '上课形式不能为空',
            'subject.required' => '科目不能为空',
        ];
        $validator = Validator::make($data,$rules,$messages);
        if ($validator->fails()) {
            $errors = $validator->errors();
            return $this->error(implode(',',$errors->all()));
        }
        // 当前用户
        $user = Auth::user();
        $data['organ_id'] = $user->id;
        $data['created_at'] = Carbon::now();
        $id = DB::table('courses')->insertGetId($data);
        if (!$id) {
            return $this->error('提交失败');
        }
        $course_info = Course::find($id);
        $course_info->number = create_course_number($id);
        $course_info->save();
        return $this->success('提交成功');
    }

    /**
     * 需求管理列表
     * @return \Illuminate\Http\JsonResponse
     */
    public function course_list()
    {
        $data = \request()->all();
        $sort = $data['sort'] ?? 'desc';
        $page_size = $data['page_size'] ?? 10;
        // 当前用户
        $user = Auth::user();
        // 角色
        $role = $data['role'] ?? 3;
        // 排序条件
        $sort_field = 'created_at';
        if (isset($data['entry_number'])) {
            $sort_field = 'entry_number';
        }
        // 筛选条件
        $where = [];
        if (isset($data['name'])) {
            $where[] = ['name','like','%'.$data['name'].'%'];
        }
        if (isset($data['grade'])) {
            $where[] = ['grade','=',$data['grade']];
        }
        if (isset($data['subject'])) {
            $where[] = ['subject','=',$data['subject']];
        }
        if (isset($data['type'])) {
            $where[] = ['type','=','type'];
        }
        if (isset($data['created_at_start']) && isset($data['created_at_end'])) {
            $where[] = ['created_at','>','created_at_start'];
            $where[] = ['created_at','<','created_at_end'];
        }
        $result = DB::table('courses')->where(['organ_id' =>$user->id,'role' => $role])->where($where)->orderBy($sort_field,$sort)->paginate($page_size);
        return $this->success('需求列表',$result);
    }

    /**
     * 需求详情
     * @return \Illuminate\Http\JsonResponse|void
     */
    public function course_detail()
    {
        $data = \request()->all();
        $course_id = $data['course_id'] ?? 0;
        // 查询是否存在
        $course_info = Course::with('users')->find($course_id);
        if (!$course_info) {
            return $this->error('课程不存在');
        }
        if ($course_info->end_time < date('Y-m-d H:i:s')) {
            $course_info->show_status = 3;
        } else {
            $course_info->show_status = $course_info->status;
        }
        dd($course_info->toArray());
    }
}
