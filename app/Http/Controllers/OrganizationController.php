<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\BaseInformation;
use App\Models\Course;
use App\Models\CourseSetting;
use App\Models\DeliverLog;
use App\Models\Message;
use App\Models\Organization;
use App\Models\OrganPrivilege;
use App\Models\OrganRole;
use App\Models\OrganRolePrivilege;
use App\Models\PlatformMessage;
use App\Models\Region;
use App\Models\SystemMessage;
use App\Models\User;
use App\Models\UserCourse;
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
use Yansongda\Pay\Pay;
use function PHPUnit\Framework\isFalse;

class OrganizationController extends Controller
{
    /**
     * 填写信息
     * @return \Illuminate\Http\JsonResponse
     */
    public function create()
    {
        $config = config('services.sms');
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
        $user->role = $data['role'] ?? 4;
        $user->organ_role_id = 1;
        // 用户编号
        if (!isset($user->number)) {
            $user->number = create_user_number($data['city_id'],$user->id);
        }
        $user->is_perfect = 1;
        $user->update();
        unset($data['role']);
        // 存入机构
        $data['created_at'] = Carbon::now();
        $data['user_id'] = $user->id;
        $data['mobile'] = $user->mobile;
        // 获取经纬度
        $province = Region::where('id',$data['province_id'])->value('region_name');
        $city = Region::where('id',$data['city_id'])->value('region_name');
        $district = Region::where('id',$data['district_id'])->value('region_name');
        $long_lat = get_long_lat($province,$city,$district,$data['address'] ?? null);
        $data['longitude'] = $long_lat[0];
        $data['latitude'] = $long_lat[1];
        $data['status'] = 0;
        Log::info('data: ',$data);
        $result = Organization::updateOrCreate(['id' => $user->organ_id],$data);
        // $id = DB::table('organizations')->insertGetId($data);
        $user->organ_id = $result->id;
        $user->update();
        $images = \request()->input('images');
        if ($images) {
            $image_data = [];
            foreach ($images as $v) {
                $image_data[] = [
                    'organ_id' => $result->id,
                    'url' => $v,
                    'created_at' => Carbon::now()
                ];
            }
            // 保存图片
            DB::table('organ_images')->insert($image_data);
        }
        // 当前时间
        $current = Carbon::now()->format('Y-m-d');
        // 查看是否有注册活动
        $invite_activity = Activity::where(['status' => 1,'type' => 1])->where('start_time', '<=', $current)
            ->where('end_time', '>=', $current)->first();
        if ($invite_activity && isset($data['parent_id'])) {
            invite_activity_log($data['parent_id'],$user->id,$user->role,$invite_activity);
        }
        // 发送通知
        if (SystemMessage::where('action',0)->value('site_message') == 1) {
            (new PlatformMessage())->saveMessage('机构入驻','机构入驻','机构端');
        }
        if (SystemMessage::where('action',0)->value('text_message') == 1) {
            $mobile = SystemMessage::where('action',0)->value('admin_mobile');
            // 发送短信
            $easySms = new EasySms($config);
            try {
                $number = new PhoneNumber($mobile);
                $easySms->send($number,[
                    'content'  => "【添添学】有新入驻的机构",
                ]);
            } catch (Exception|NoGatewayAvailableException $exception) {
                return $this->error($exception->getResults());
            }
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
        if ($user->role == 4) {
            $type = 5;
            $city_id = $user->organization->city_id;
            $province_id = $user->organization->province_id;
            $district_id = $user->organization->district_id;
        } else {
            $type = 1;
            $city_id = $user->city_id;
            $province_id = $user->province_id;
            $district_id = $user->district_id;
        }

        // 查询服务费
        $service_price = get_service_price(2,$province_id,$city_id,$district_id);
        Log::info('service_price: '.$service_price);
        // $service_price = 0.01;
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
        return $this->success('生成订单成功',compact('out_trade_no','service_price'));
    }

    /**
     * 发布需求
     * @return \Illuminate\Http\JsonResponse
     */
    public function create_course()
    {
        $config = config('services.sms');
        $data = \request()->all();
        Log::info('post_data: ',$data);
        $rules = [
            'name' => 'required',
            'method' => 'required',
            'introduction' => 'required',
        ];
        $messages = [
            'name.required' => '名称不能为空',
            'method.required' => '授课方式不能为空',
            'introduction.required' => '课程详情不能为空',
        ];
        $validator = Validator::make($data,$rules,$messages);
        if ($validator->fails()) {
            $errors = $validator->errors();
            return $this->error(implode(',',$errors->all()));
        }
        // 查询招教师需求最大的有效期
        $system_valid_time = CourseSetting::where(['role' => $data['role']])->orderByDesc('created_at')->value('end_time');
        if ($data['valid_time'] > $system_valid_time) {
            return $this->error('有效期不能超过'.$system_valid_time.'天');
        }
        // 当前用户
        $user = Auth::user();
        $data['organ_id'] = $user->organization->id;
        $data['created_at'] = Carbon::now();
        $data['adder_role'] = 4;
        $data['adder_id'] = $user->id;
        $data['adder_name'] = $user->organization->name;
        $data['end_time'] = Carbon::now()->setTime(23,59,59)->addDays($data['valid_time']);
        $data['is_on'] = 0;
        // $data['introduction'] = nl2br($data['introduction']);
        if ($data['method'] !== '线上') {
            $location = get_location($data['longitude'],$data['latitude']);
            $data['province'] = Region::where(['region_name' => $location['province']])->value('id');
            $data['city'] = Region::where(['region_name' => $location['city'],'parent_id' => $data['province']])->value('id');
            $data['district'] = Region::where(['region_name' => $location['district'],'parent_id' => $data['city']])->value('id');
        }
        $id = DB::table('courses')->insertGetId($data);
        if (!$id) {
            return $this->error('提交失败');
        }
        $course_info = Course::find($id);
        $course_info->number = new_create_course_number($id,$data['method'],4, $data['role']);
        $course_info->save();
        // 发送通知
        if (SystemMessage::where('action',7)->value('site_message') == 1) {
            (new PlatformMessage())->saveMessage('发布需求','发布需求','机构端');
        }
        if (SystemMessage::where('action',7)->value('text_message') == 1) {
            $mobile = SystemMessage::where('action',7)->value('admin_mobile');
            // 发送短信
            $easySms = new EasySms($config);
            try {
                $number = new PhoneNumber($mobile);
                $easySms->send($number,[
                    'content'  => "【添添学】有新发布的需求",
                ]);
            } catch (Exception|NoGatewayAvailableException $exception) {
                return $this->error($exception->getResults());
            }
        }
        /*if (SystemMessage::where('action',7)->value('official_account') == 1) {
            // 推送公众号消息
            send_official_message();
        }*/
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
        $status = $data['status'] ?? 0;
        // 当前用户
        $user = Auth::user();
        // 查询当前用户的机构
        $organ_id = $user->organ_id;
        // 机构成员
        $organ_users = User::where('organ_id',$organ_id)->get()->pluck('id')->toArray();
        // 角色
        $role = $data['role'] ?? 3;
        // 排序条件
        $sort_field = 'created_at';
        if (isset($data['entry_number'])) {
            $sort_field = 'entry_number';
        }
        if (isset($data['sort_buyer_count'])) {
            $sort_field = 'buyer_count';
        }
        if (isset($data['sort_visit_count'])) {
            $sort_field = 'visit_count';
        }
        // 筛选条件
        $where = [];
        if (isset($data['name'])) {
            $where[] = ['name','like','%'.$data['name'].'%'];
        }
        /*if (isset($data['grade'])) {
            $where[] = ['grade','=',$data['grade']];
        }
        if (isset($data['subject'])) {
            $where[] = ['subject','=',$data['subject']];
        }
        if (isset($data['type'])) {
            $where[] = ['type','=',$data['type']];
        }*/
        if (isset($data['create_at_start']) && isset($data['created_at_end'])) {
            $where[] = ['created_at','>',$data['create_at_start']];
            $where[] = ['created_at','<',$data['created_at_end']];
        }
        if (isset($data['course_status'])) {
            if ($data['course_status'] == 1) {
                $where[] = ['is_invalid','=',1];
            } else {
                $where[] = ['is_invalid','!=',1];
            }

        }

        $result = Course::with('adder')->where(['adder_role' => 4,'role' => $role,'status' => $status])->where($where)->whereIn('adder_id',$organ_users)->orderBy($sort_field,$sort)->logListenedSql()->paginate($page_size);
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
        $course_info = Course::with('organization')->find($course_id);
        if (!$course_info) {
            return $this->error('课程不存在');
        }
        // 当前用户
        $user = Auth::user();
        if ($course_info->adder_role == 0) {
            $course_info->visit_count++;
            $course_info->update();
        } else {
            if ($course_info->adder_id !== $user->id) {
                $course_info->visit_count++;
                $course_info->update();
            }
        }
        if ($course_info->end_time < date('Y-m-d H:i:s')) {
            $course_info->status = 2;
            $course_info->save();
        }
        if (!in_array($course_info->status,[0,2])) {
            // 查询投递人数
            $course_info->deliver_count = $course_info->deliver->count();
        }
        /*$course_info->class_date = json_decode($course_info->class_date,true);
        $course_info->total_price = $course_info->class_price * $course_info->class_number;*/
        if (in_array($course_info->course_status,[4,5])) {
            $delivers = $course_info->deliver->filter(function ($item) {
                if ($item->pay_status == 1) {
                    return $item;
                }
            })->first();
            $course_info->teacher_name = $delivers ? $delivers->user->name : '';
            $course_info->teacher_mobile = $delivers ? $delivers->user->mobile : '';
        } else {
            $course_info->teacher_name = null;
            $course_info->teacher_mobile = null;
        }
        return $this->success('课程详情',$course_info);
    }

    /**
     * 编辑需求
     * @return \Illuminate\Http\JsonResponse
     */
    public function update_course()
    {
        $config = config('services.sms');
        $data = \request()->all();
        $id = $data['id'] ?? 0;
        // 查询课程
        $course_info = Course::find($id);
        if (!$course_info) {
            return $this->error('课程不存在');
        }
        $rules = [
            'name' => 'required',
            'method' => 'required',
            'introduction' => 'required',
        ];
        $messages = [
            'name.required' => '名称不能为空',
            'method.required' => '授课方式不能为空',
            'introduction.required' => '课程详情不能为空',
        ];
        $validator = Validator::make($data,$rules,$messages);
        if ($validator->fails()) {
            $errors = $validator->errors();
            return $this->error(implode(',',$errors->all()));
        }
        // 查询招教师需求最大的有效期
        $system_valid_time = CourseSetting::where(['role' => $data['role']])->orderByDesc('created_at')->value('end_time');
        if ($data['valid_time'] > $system_valid_time) {
            return $this->error('有效期不能大于系统设置时间');
        }
        // 更新数据
        $data['status'] = 0;
        $data['updated_at'] = Carbon::now();
        $data['end_time'] = Carbon::now()->setTime(23,59,59)->addDays($data['valid_time']);
        $result = DB::table('courses')->where('id',$id)->update($data);
        if (!$result) {
            return $this->error('编辑失败');
        }
        // 发送通知
        if (SystemMessage::where('action',7)->value('site_message') == 1) {
            (new PlatformMessage())->saveMessage('发布需求','发布需求','机构端');
        }
        if (SystemMessage::where('action',7)->value('text_message') == 1) {
            $mobile = SystemMessage::where('action',7)->value('admin_mobile');
            // 发送短信
            $easySms = new EasySms($config);
            try {
                $number = new PhoneNumber($mobile);
                $easySms->send($number,[
                    'content'  => "【添添学】有新发布的需求",
                ]);
            } catch (Exception|NoGatewayAvailableException $exception) {
                return $this->error($exception->getResults());
            }
        }
        return $this->success('编辑成功');
    }

    /**
     * 投递教师
     * @return \Illuminate\Http\JsonResponse
     */
    public function deliver_teachers()
    {
        $data = \request()->all();
        $course_id = $data['course_id'] ?? 0;
        $page_size = $data['page_size'] ?? 10;
        // 投递列表
        $result = DeliverLog::with(['user'])->where('course_id',$course_id)->paginate($page_size);
        foreach ($result as $v) {
            $v->teacher_info = $v->user->teacher_info;
            $v->teacher_education = $v->user->teacher_education;
            $v->subject = $v->course->subject;
            $v->teacher_tags = $v->user->teacher_tags->isEmpty() ? ['未填写'] : $v->user->teacher_tags->pluck('tag');
            $v->deliver_detail = $v->user->deliver_log;
        }
        return $this->success('投递教师列表',$result);
    }

    /**
     * 报名学生列表
     * @return \Illuminate\Http\JsonResponse
     */
    public function entry_students()
    {
        $data = \request()->all();
        $course_id = $data['course_id'] ?? 0;
        $page_size = $data['page_size'] ?? 10;
        // 投递列表
        $result = User::with('student_course')->whereHas('student_course', function ($query) use ($course_id) {
            $query->where('course_id', $course_id);
        })->paginate($page_size);
        foreach ($result as $v) {
            $user_course = UserCourse::where(['user_id' => $v->id,'course_id' => $course_id])->first();
            $v->pay_status = $user_course->status;
            $v->out_trade_no = $user_course->out_trade_no;
        }
        // 支付总人数
        $payed_total = $result->filter(function ($item) {
            return $item['pay_status'] == 1;
        })->count();
        return $this->success('报名学生列表',compact('result','payed_total'));
    }

    /**
     * 机构中心
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        // 当前机构
        $user = Auth::user();
        $balance = $user->withdraw_balance;
        $income = $user->total_income;
        $unread_message = $user->messages()->where('status',0)->count();
        return $this->success('机构中心',compact('balance','income','unread_message'));
    }

    /**
     * 详情
     * @return \Illuminate\Http\JsonResponse
     */
    public function detail()
    {
        $data = \request()->all();
        // 当前机构
        $user = Auth::user();
        $balance = $user->withdraw_balance;
        $income = $user->total_income;
        // 收支明细
        $bills = $user->bills()->take(5)->get();
        return $this->success('详情',compact('balance','income','bills'));
    }

    /**
     * 角色列表
     * @return \Illuminate\Http\JsonResponse
     */
    public function role_list()
    {
        $data = \request()->all();
        $page_size = $data['page_size'] ?? 10;
        // 查询角色列表
        $role_list = OrganRole::whereNotIn('id',[1])->paginate($page_size);
        foreach ($role_list as $v) {
            $v->user_count = $v->users()->count();
            $v->has_privilege = $v->privileges()->exists();
        }
        return $this->success('角色列表',$role_list);
    }

    /**
     * 角色详情
     * @return \Illuminate\Http\JsonResponse
     */
    public function role_detail()
    {
        $data = \request()->all();
        $role_id = $data['role_id'] ?? 0;
        $role_info = OrganRole::with('privileges')->find($role_id);
        if (!$role_id) {
            return $this->error('角色不存在');
        }
        return $this->success('角色详情',$role_info);
    }

    /**
     * 权限列表
     * @return \Illuminate\Http\JsonResponse
     */
    public function privilege_list()
    {
        $data = \request()->all();
        $page_size = $data['page_size'] ?? 0;
        $result = OrganPrivilege::paginate($page_size);
        return $this->success('权限列表',$result);
    }

    /**
     * 分配权限
     * @return \Illuminate\Http\JsonResponse
     */
    public function add_role_privilege()
    {
        $data = \request()->all();
        $role_id = $data['role_id'] ?? 0;
        if (!OrganRole::find($role_id)) {
            return $this->error('角色不存在');
        }
        // 分配权限
        $insert_data = [];
        // 删除当前角色的所有权限
        OrganRolePrivilege::where('role_id',$role_id)->delete();
        foreach ($data['privileges'] as $v) {
            $insert_data[] = [
                'role_id' => $role_id,
                'privilege_id' => $v,
                'created_at' => Carbon::now(),
            ];
        }
        // 保存权限
        $result = DB::table('organ_role_privilege')->insert($insert_data);
        if (!$result) {
            return $this->error('保存失败');
        }
        return $this->success('保存成功');
    }

    /**
     * 添加角色
     * @return \Illuminate\Http\JsonResponse
     */
    public function add_role()
    {
        $data = \request()->all();
        $role_id = $data['role_id'] ?? 0;
        $rules = [
            'name' => 'required',
            'description' => 'required',
            'privileges' => 'required'
        ];
        $messages = [
            'name.required' => '名称不能为空',
            'description.required' => '描述不能为空',
            'privileges.required' => '权限不能为空',
        ];
        $validator = Validator::make($data,$rules,$messages);
        if ($validator->fails()) {
            $errors = $validator->errors();
            return $this->error(implode(',',$errors->all()));
        }
        // 保存角色
        $role_info = OrganRole::find($role_id);
        if ($role_info) {
            $role_info->name = $data['name'];
            $role_info->description = $data['description'];
            $role_info->save();
            // 删除权限
            OrganRolePrivilege::where('role_id',$role_id)->delete();
            $id = $role_id;
        } else {
            $role_data = [
                'name' => $data['name'],
                'description' => $data['description'],
                'created_at' => Carbon::now()
            ];
            $id = DB::table('organ_roles')->insertGetId($role_data);
        }
        $privilege_data = [];
        foreach ($data['privileges'] as $v) {
            $privilege_data[] = [
                'role_id' => $id,
                'privilege_id' => $v,
                'created_at' => Carbon::now(),
            ];
        }
        $result = DB::table('organ_role_privilege')->insert($privilege_data);
        if (!$result) {
            return $this->error('保存失败');
        }
        return $this->success('保存成功');
    }

    /**
     * 删除角色
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete_role()
    {
        $data = \request()->all();
        $role_id = $data['role_id'] ?? 0;
        $role_info = OrganRole::with('users')->find($role_id);
        if (!$role_info) {
            return $this->error('角色不存在');
        }
        if (isset($role_info->users) && $role_info->users->count() > 0) {
            return $this->error('请先移除角色下的所有用户');
        }
        // 删除角色
        DB::transaction(function () use ($role_info) {
            // 删除角色
            $role_info->delete();
            $role_info->privileges()->detach();
        });
        return $this->success('操作成功');
    }

    /**
     * 成员列表
     * @return \Illuminate\Http\JsonResponse
     */
    public function member()
    {
        $data = \request()->all();
        $page_size = $data['page_size'] ?? 10;
        // 当前用户
        $user = Auth::user();
        $members = User::with('organ_role')->where(['role' => 4,'organ_id' => $user->organ_id])->whereNotIn('id',[$user->id])->paginate($page_size);
        // dd($members->toArray());
        foreach ($members as $member) {
            $member->role_name = $member->organ_role->name;
        }
        return $this->success('成员列表',$members);
    }

    /**
     * 成员详情
     * @return \Illuminate\Http\JsonResponse
     */
    public function member_detail()
    {
        $data = \request()->all();
        $user_id = $data['user_id'] ?? 0;
        $user_info = User::with('organ_role')->find($user_id);
        if (!$user_info) {
            return $this->error('用户不存在');
        }
        if ($user_info->role !== 4) {
            return $this->error('该用户不是机构成员');
        }
        return $this->success('成员详情',$user_info);
    }

    /**
     * 删除&禁用启用
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete_member()
    {
        $data = \request()->all();
        $user_id = $data['user_id'] ?? 0;
        $type = $data['type'] ?? 0;
        Log::info('type: '.$type);
        $user_info = User::find($user_id);
        // 当前用户
        $user = Auth::user();
        if (!$user_info) {
            return $this->error('用户不存在');
        }
        if ($user_info->role !== 4) {
            return $this->error('该用户不是机构成员');
        }
        if ($user_id == $user->id) {
            return $this->error('您不能禁用自己');
        }
        if ($type == 0) {
            // 删除用户
            $user_info->delete();
        } else {
            // 禁用&启用
            $user_info->status = $user_info->status == 2 ? 1 : 2;
            $user_info->save();
        }
        return $this->success('操作成功');
    }

    /**
     * 编辑机构成员
     * @return \Illuminate\Http\JsonResponse
     */
    public function update_member()
    {
        $data = \request()->all();
        $user_id = $data['user_id'] ?? 0;
        $rules = [
            'name' => 'required',
            'gender' => 'required',
            'organ_role_id' => 'required'
        ];
        $messages = [
            'name.required' => '姓名不能为空',
            'gender.required' => '性别不能为空',
            'organ_role_id.required' => '角色不能为空'
        ];
        $validator = Validator::make($data,$rules,$messages);
        if ($validator->fails()) {
            $error = $validator->errors();
            return $this->error(implode(',',$error->all()));
        }
        $user_info = User::find($user_id);
        if (!$user_info) {
            return $this->error('用户不存在');
        }
        if ($user_info->role !== 4) {
            return $this->error('该用户不是机构成员');
        }
        // 更新用户
        $user_info->name = $data['name'];
        $user_info->gender = $data['gender'];
        $user_info->organ_role_id = $data['organ_role_id'];
        $result = $user_info->save();
        if (!$result) {
            return $this->error('操作失败');
        }
        return $this->success('操作成功');
    }

    /**
     * 教师管理
     * @return \Illuminate\Http\JsonResponse
     */
    public function teachers()
    {
        $data = \request()->all();
        $page_size = $data['page_size'] ?? 10;
        $district_id = $data['district_id'] ?? 0;
        if (isset($data['longitude']) && isset($data['latitude'])) {
            // 根据经纬度获取省市区
            $location = get_location($data['longitude'],$data['latitude']);
            if (!$location) {
                return $this->error('定位出错');
            }
            $district_id = Region::where('code',$location['adcode'])->value('id');
        }
        // 当前机构
        $user = Auth::user();
        // 用户所在机构
        $organ_id = $user->organ_id;
        // 机构所有的成员
        $organ_user = User::where('organ_id',$organ_id)->get()->pluck('id')->toArray();
        // 机构购买的教师
        $teacher_ids = UserTeacherOrder::whereIn('user_id',$organ_user)->where('status',1)->distinct()->pluck('teacher_id');
        // 排序
        $order = $data['order'] ?? 'desc';
        $sort_field = 'users.age';
        if (isset($data['sort_teaching_year'])) {
            $sort_field = 'teacher_info.teaching_year';
        } elseif (isset($data['sort_education'])) {
            $sort_field = 'teacher_info.education_id';
        }
        // 筛选
        $where = [];
        if (isset($data['city_id'])) {
            $where[] = ['users.city_id','=',$data['city_id']];
        }
        if (isset($data['name'])) {
            $where[] = ['users.name','like','%'.$data['name'].'%'];
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
            $where[] = ['teacher_info.highest_education','=',$data['filter_education']];
        }
        if (isset($data['filter_teaching_year_min']) && isset($data['filter_teaching_year_max'])) {
            $where[] = ['teacher_info.teaching_year','>',$data['filter_teaching_year_min']];
            $where[] = ['teacher_info.teaching_year','<',$data['filter_teaching_year_max']];
        }
        if (isset($data['filter_is_auth'])) {
            $where[] = ['users.is_real_auth','=',$data['filter_is_auth']];
        }
        if (isset($data['keyword'])) {
            $where[] = ['users.name','like','%'.$data['keyword'].'%'];
        }
        $result = User::with(['teacher_education','teacher_info','teacher_career'])->where($where)->whereIn('users.id',$teacher_ids)->orderBy($sort_field,$order)->paginate($page_size);
        /*$result = User::leftJoin('teacher_info', 'users.id', '=', 'teacher_info.user_id')
            ->leftJoin('teacher_education','users.id','=','teacher_education.user_id')
            ->leftJoin('teacher_career','users.id','=','teacher_career.user_id')
            ->where($where)
            // ->where(['district_id' => $district_id])
            ->whereIn('users.id',$teacher_ids)
            ->orderBy($sort_field,$order)
            ->select('users.*','teacher_education.highest_education','teacher_education.graduate_school','teacher_info.teaching_year','teacher_career.subject','teacher_info.picture')
            ->paginate($page_size);*/
        foreach ($result as $v) {
            // 科目
            $subject = $v->teacher_career->pluck('subject')->toArray();
            $v->subject = count($subject) > 0 ? handel_subject(implode(',',$subject)) : [];
        }
        return $this->success('教师列表',$result);
    }

    /**
     * 批量支付
     * @return \Illuminate\Http\JsonResponse
     */
    public function batch_pay()
    {
        $config = config('pay');
        $data = \request()->all();
        $out_trade_no_arr = $data['out_trade_no'] ?? [];
        $total_out_trade_no = app('snowflake')->id();
        $total_amount = 0;
        foreach ($out_trade_no_arr as $v) {
            $order_info = UserCourse::where('out_trade_no',$v)->first();
            if ($order_info->status == 1) {
                return $this->error('该订单已被支付');
            }
            $order_info->total_out_trade_no = $total_out_trade_no;
            $total_amount += $order_info->amount;
            $order_info->update();
        }
        // 当前用户
        $user = Auth::user();
        if ($total_amount <= $user->withdraw_balance) {
            foreach ($out_trade_no_arr as $v) {
                UserCourse::where('out_trade_no',$v)->update(['status' => 1]);
            }
            $user->withdraw_balance -= $total_amount;
            $user->update();
            $result = '余额支付';
        } else {
            // 调起支付
            $pay_data = [
                'out_trade_no' => $total_out_trade_no,
                'description' => '服务费',
                'amount' => [
                    'total' => $total_amount * 100,
                    'currency' => 'CNY',
                ],
                'payer' => [
                    'openid' => $user->open_id,
                ],
                '_config' => 'organization',
            ];
            $result = Pay::wechat($config)->mini($pay_data);
        }
        return $this->success('调起支付',compact('result'));
    }

    /**
     * 获取金额
     * @return \Illuminate\Http\JsonResponse
     */
    public function get_total_amount()
    {
        $data = \request()->all();
        $amount = 0;
        // 当前用户
        $user = Auth::user();
        // 余额
        $balance = $user->withdraw_balance;
        if (is_array($data['out_trade_no'])) {
            foreach ($data['out_trade_no'] as $v ){
                $order = UserCourse::where('out_trade_no',$v)->first();
                $amount += $order->amount;
            }
        } else {
            $order = UserCourse::where('out_trade_no',$data['out_trade_no'])->first();
            $amount = $order->amount;
        }
        if ($balance < $amount && $balance > 0) {
            $discount_amount = $balance;
        } else if ($balance == 0) {
            $discount_amount = 0;
        } else {
            $discount_amount = $amount;
        }
        $actual_amount = max(($amount - $balance), 0);
        return $this->success('获取金额',compact('discount_amount','actual_amount','amount'));
    }

    /**
     * 单个支付
     * @return \Illuminate\Http\JsonResponse
     */
    public function pay()
    {
        $config = config('pay');
        $data = \request()->all();
        $out_trade_no = $data['out_trade_no'] ?? '';
        // 查询订单
        $order = UserCourse::where('out_trade_no',$out_trade_no)->first();
        if (!$order) {
            return $this->error('订单不存在');
        }
        if ($order->status == 2) {
            return $this->error('该订单已关闭');
        }
        if ($order->status == 1) {
            return $this->error('该订单已被支付');
        }
        // 当前用户
        $user = Auth::user();
        // 余额
        $balance = $user->withdraw_balance;
        if ($order->amount <= $balance) {
            $order->status = 1;
            $user->withdraw_balance -= $order->amount;
            $order->update();
            $user->update();
            $result = '余额支付';
            // 保存日志
            $log_data = [
                'user_id' => $user->id,
                'amount' => '-'.$order->amount,
                'type' => 10,
                'description' => '查看报名',
                'created_at' => Carbon::now()
            ];
            DB::table('bills')->insert($log_data);
        } else {
            // 调起支付
            $pay_data = [
                'out_trade_no' => $out_trade_no,
                'description' => '服务费',
                'amount' => [
                    'total' => ($order->amount - $balance) * 100,
                    'currency' => 'CNY',
                ],
                'payer' => [
                    'openid' => $user->open_id,
                ],
                '_config' => 'organization',
            ];
            Log::info('data:',$pay_data);
            $result = Pay::wechat($config)->mini($pay_data);
        }
        return $this->success('调起支付',compact('result'));
    }

    /**
     * 机构信息
     * @return \Illuminate\Http\JsonResponse
     */
    public function info()
    {
        // 当前用户
        $user = Auth::user();
        // 机构信息
        $organization = $user->organization;
        $organization->province_name = $organization->province->region_name;
        $organization->city_name = $organization->city->region_name;
        $organization->ditrict_name = $organization->district->region_name;
        $organization->images = $organization->image_info->pluck('url');
        $organization->organ_role_id = $user->organ_role_id;
        $organization->mobile = $user->mobile;
        return $this->success('机构信息',$organization);
    }

    /**
     * 成员权限
     * @return \Illuminate\Http\JsonResponse
     */
    public function get_member_privilege()
    {
        $user = Auth::user();
        // 员工权限
        $privileges = $user->organ_role->privileges->pluck('name');
        // 获取所有权限
        $all_privileges = OrganPrivilege::all()->pluck('name');

        // 使用 map 方法遍历集合 B，并构建新的关联数组
        $result = $all_privileges->map(function ($item) use ($privileges) {
            return [
                $item => $privileges->contains($item)
            ];
        })->collapse();
        $return = $result->all();
        return $this->success('成员权限',$return);
    }
}
