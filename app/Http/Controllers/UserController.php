<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\BaseInformation;
use App\Models\Bill;
use App\Models\Collect;
use App\Models\Course;
use App\Models\DeliverLog;
use App\Models\Message;
use App\Models\Organization;
use App\Models\ParentStudent;
use App\Models\PlatformMessage;
use App\Models\Region;
use App\Models\SystemMessage;
use App\Models\TeacherCareer;
use App\Models\TeacherCert;
use App\Models\TeacherCourseOrder;
use App\Models\TeacherEducation;
use App\Models\TeacherInfo;
use App\Models\TeacherRealAuth;
use App\Models\TeacherTag;
use App\Models\User;
use App\Models\UserContact;
use App\Models\UserCourse;
use App\Models\UserTeacherOrder;
use App\Models\Withdraw;
use Carbon\Carbon;
use EasyWeChat;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Validator;
use Overtrue\EasySms\EasySms;
use Overtrue\EasySms\Exceptions\Exception;
use Overtrue\EasySms\Exceptions\NoGatewayAvailableException;
use Overtrue\EasySms\PhoneNumber;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class UserController extends Controller
{
    public function update_info()
    {
        $config = config('services.sms');
        $data = \request()->all();
        $role = $data['role'] ?? 1;
        $rules = [
            'avatar' => 'required|url',
            'nickname' => 'regex:/^[\p{Han}a-zA-Z]+$/u|max:10',
            'name' => 'required|regex:/^[\p{Han}a-zA-Z]+$/u|max:10',
            'gender' => 'required',
            'province_id' => 'required',
            'city_id' => 'required',
            'district_id' => 'required',
        ];
        $messages = [
            'avatar.required' => '头像不能为空',
            'avatar.url' => '头像格式错误',
            'nickname.regex' => '昵称只能为汉字或英文',
            'nickname.max' => '昵称最多为20个字符',
            'name.required' => '名称不能为空',
            'name.regex' => '名称只能为汉字或英文',
            'name.max' => '名称最多为20个字符',
            'gender.required' => '性别不能为空',
            'province_id.required' => '省份不能为空',
            'city_id.required' => '城市不能为空',
            'district_id.required' => '区县不能为空',
        ];
        if ($role == 1) {
            $rules['grade'] = 'required';
            $messages['grade.required'] = '年级不能为空';
        }
        $validator = Validator::make($data,$rules,$messages);
        if ($validator->fails()) {
            $error = $validator->errors();
            return $this->error(implode(',',$error->all()));
        }
        $user = Auth::user();
        // 用户编号
        if (!isset($user->number)) {
            $data['number'] = create_user_number($data['city_id'],$user->id);
        }
        if ($user->role != 3) {
            $data['age'] = Carbon::parse($data['birthday'])->diffInYears(Carbon::now());
        }
        if ($role == 4) {
            // 机构信息
            $organization = Organization::where('user_id',$user->id)->first();
            $organization->latitude = $data['latitude'] ?? '';
            $organization->longitude = $data['longitude'] ?? '';
            $organization->update();
            $user->organ_id = $organization->id;
            $user->update();
            unset($data['latitude']);
            unset($data['longitude']);
        }
        if (!isset($user->created_at)) {
            $data['created_at'] = Carbon::now();
        }
        if ($role == 2) {
            $province = Region::where('id',$data['province_id'])->value('region_name');
            $city = Region::where('id',$data['city_id'])->value('region_name');
            $district = Region::where('id',$data['district_id'])->value('region_name');
            $location_data = get_long_lat($province,$city,$district,$data['address'] ?? '');
            $data['longitude'] = $location_data[0];
            $data['latitude'] = $location_data[1];
        }
        $data['is_perfect'] = 1;
        $result = DB::table('users')->where('id',$user->id)->update($data);
        if (!$result) {
            return $this->error('更新失败');
        }
        // 更新教师标签
        if ($role == 3) {
            $tag = '资料完善';
            $tag_info = [
                'user_id' => $user->id,
                'tag' => $tag,
                'type' => 2
            ];
            TeacherTag::updateOrCreate(['user_id' => $user->id,'tag' => $tag],$tag_info);
            // 发送通知
            if (SystemMessage::where('action',3)->value('site_message') == 1) {
                (new PlatformMessage())->saveMessage('教师资料更新','教师资料更新','教师端');
            }
            if (SystemMessage::where('action',3)->value('text_message') == 1) {
                $admin_mobile = SystemMessage::where('action',3)->value('admin_mobile');
                // 发送短信
                $easySms = new EasySms($config);
                $text = '教师资料';
                try {
                    $admin_number = new PhoneNumber($admin_mobile);
                    $easySms->send($admin_number,[
                        'content'  => "【添添学】".$text."更新",
                    ]);
                } catch (Exception|NoGatewayAvailableException $exception) {
                    return $this->error($exception->getResults());
                }
            }
        }
        // 当前时间
        $current = Carbon::now()->format('Y-m-d');
        if ($user->is_new == 1) {
            // 查看是否有注册活动
            $invite_activity = Activity::where(['status' => 1,'type' => 1])->where('start_time', '<=', $current)
                ->where('end_time', '>=', $current)->first();
            if ($invite_activity && isset($user->parent_id)) {
                invite_activity_log($user->parent_id,$user->id,$role,$invite_activity);
            }
        }
        $user->is_new = 0;
        $user->update();
        return $this->success('更新成功');
    }

    /**
     * 绑定手机号
     * @return \Illuminate\Http\JsonResponse
     */
    public function bind_mobile()
    {
        $data = \request()->all();
        $rules = [
            'mobile' => 'required|phone_number',
            'code' => 'required'
        ];
        $messages = [
            'mobile.required' => '手机号不能为空',
            'mobile.phone_number' => '手机号格式不正确',
            'code.required' => '验证码不能为空'
        ];
        $validator = Validator::make($data,$rules,$messages);
        if ($validator->fails()) {
            $error = $validator->errors();
            return $this->error(implode(',',$error->all()));
        }
        // 校验验证码
        $sendcode = Redis::get($data['mobile']);
        if(!$sendcode || $sendcode!=$data['code']) return $this->error('验证码不正确');
        $user = Auth::user();
        $mobile = $data['mobile'];
        $user->mobile = $mobile;
        $user->save();
        return $this->success('绑定成功');
    }

    /**
     * 添加联系人
     * @return \Illuminate\Http\JsonResponse
     */
    public function add_contacts()
    {
        $data = \request()->all();
        $rules = [
            'name' => 'required',
            'mobile' => 'required|phone_number',
            'relation' => 'required'
        ];
        $messages = [
            'name.required' => '姓名不能为空',
            'mobile.required' => '手机号不能为空',
            'relation.required' => '关系不能为空'
        ];
        $validator = Validator::make($data,$rules,$messages);
        if ($validator->fails()) {
            $error = $validator->errors();
            return $this->error(implode(',',$error->all()));
        }
        $user_id = Auth::id();
        /*// 查询是否存在
        $contact_info = UserContact::where(['user_id' => $user_id,'name' => $data['name'],'mobile' => $data['mobile'],'relation' => $data['relation']])->first();
        if ($contact_info) {
            return $this->error('联系人已存在');
        }*/
        $data['user_id'] = Auth::id();
        $data['created_at'] = Carbon::now();
        if (isset($data['id'])) {
            $result = UserContact::where('id',$data['id'])->update($data);
        } else {
            $result = UserContact::create($data);
        }
        // $result = DB::table('user_contacts')->insert($data);
        if (!$result) {
            return $this->error('操作失败');
        }
        return $this->success('操作成功');
    }

    /**
     * 联系人
     * @return \Illuminate\Http\JsonResponse
     */
    public function get_contacts()
    {
        $user_id = Auth::id();
        $contacts = UserContact::where('user_id',$user_id)->orderBy('created_at','desc')->get();
        return $this->success('联系人',$contacts);
    }

    /**
     * 删除联系人
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete_contact()
    {
        $data = \request()->all();
        $id = $data['id'] ?? 0;
        $contact = UserContact::find($id);
        if (!$contact) {
            return $this->error('联系人不存在');
        }
        // 当前用户
        $user = Auth::user();
        if ($user->id !== $contact->user_id) {
            return $this->error('这不是您的联系人，您无权删除');
        }
        $contact->delete();
        return $this->success('删除成功');
    }

    /**
     * 家长添加学生
     * @return \Illuminate\Http\JsonResponse
     */
    public function add_students()
    {
        $data = \request()->all();
        $rules = [
            'name' => 'required|regex:/^[\p{Han}a-zA-Z]+$/u|max:20',
            'gender' => 'required',
            'grade' => 'required',
        ];
        $messages = [
            'name.required' => '姓名不能为空',
            'name.regex' => '姓名只能为汉字或英文',
            'name.max' => '姓名最多为20个字符',
            'gender.required' => '性别不能为空',
            'grade.required' => '年级不能为空'
        ];
        $validator = Validator::make($data,$rules,$messages);
        if ($validator->fails()) {
            $error = $validator->errors();
            return $this->error(implode(',',$error->all()));
        }
        $user_id = Auth::id();
        // 查询是否存在
        $student_info = ParentStudent::where(['user_id' => $user_id,'name' => $data['name'],'gender' => $data['gender'],'grade' => $data['grade']])->first();
        if ($student_info) {
            return $this->error('学生已存在');
        }
        $data['user_id'] = $user_id;
        $data['created_at'] = Carbon::now();
        $result = DB::table('parent_students')->insert($data);
        if (!$result) {
            return $this->error('添加失败');
        }
        return $this->success('添加成功');
    }

    /**
     * 学生列表
     * @return \Illuminate\Http\JsonResponse
     */
    public function student_list()
    {
        $user_id = Auth::id();
        $result = ParentStudent::where('user_id',$user_id)->orderBy('created_at','desc')->get();
        return $this->success('学生列表',$result);
    }

    /**
     * 完善资料-教师
     * @return \Illuminate\Http\JsonResponse
     */
    public function update_teacher_info()
    {
        $data = \request()->all();
        $user_id = Auth::id();
        if (isset($data['info'])) {
            $info_data = [
                'user_id' => $user_id,
                'id_card_front' => $data['info']['id_card_front'] ?? '',
                'id_card_backend' => $data['info']['id_card_backend'] ?? '',
                'picture' => $data['info']['picture'] ?? '',
            ];
            TeacherInfo::updateOrCreate(['user_id' => $user_id],$info_data);
        }

        if (isset($data['education'])) {
            $education_data = [
                'user_id' => $user_id,
                'highest_education' => $data['education']['highest_education'] ?? '',
                'graduate_school' => $data['education']['graduate_school'] ?? '',
                'speciality' => $data['education']['speciality'] ?? '',
                'graduate_cert' => $data['education']['graduate_cert'] ?? '',
                'diploma' => $data['education']['diploma'] ?? '',
            ];
            TeacherEducation::updateOrCreate(['user_id' => $user_id],$education_data);
        }

        if (isset($data['cert'])) {
            $cert_data = [
                'user_id' => $user_id,
                'teacher_cert' =>  is_array($data['cert']['teacher_cert']) ? json_encode($data['cert']['teacher_cert']) : json_encode([$data['cert']['teacher_cert']]),
                'other_cert' => is_array($data['cert']['other_cert']) ? json_encode($data['cert']['other_cert']) : json_encode([$data['cert']['other_cert']]),
                'honor_cert' => is_array($data['cert']['honor_cert']) ? json_encode($data['cert']['honor_cert']) : json_encode([$data['cert']['honor_cert']]),
            ];
            TeacherCert::updateOrCreate(['user_id' => $user_id],$cert_data);
        }
        return $this->success('提交成功');
    }

    /**
     * 教学经历-教师
     * @return \Illuminate\Http\JsonResponse
     */
    public function teaching_experience()
    {
        $config = config('services.sms');
        $data = \request()->all();
        $id = $data['id'] ?? 0;
        $user_id = Auth::id();
        $data['user_id'] = $user_id;
        // 查询是否存在
        $data['created_at'] = Carbon::now();
        $data['status'] = 0;
        Log::info('teaching_type: ',$data['teaching_type']);
        $data['teaching_type'] = implode(',',$data['teaching_type']);
        $data['subject'] = implode(',',$data['subject']);
        $data['object'] = implode(',',$data['object']);
        Log::info('data: ',$data);
        $result = TeacherCareer::updateOrCreate(['id' => $id],$data);
        if (!$result) {
            return $this->error('保存失败');
        }
        // 计算教龄
        $teacher_experience = TeacherCareer::where('user_id',$user_id)->get();
        $teaching_year = 0;
        foreach ($teacher_experience as $value) {
            $start_time = Carbon::parse($value->start_time);
            $end_time = Carbon::parse($value->end_time);
            $teaching_years = $start_time->diffInYears($end_time);
            $teaching_year += $teaching_years;
        }
        // 更新教龄
        DB::table('teacher_info')->where('user_id',$user_id)->update(['teaching_year' => $teaching_year]);
        if (!$result) {
            return $this->error('提交失败');
        }
        // 发送通知
        if (SystemMessage::where('action',3)->value('site_message') == 1) {
            (new PlatformMessage())->saveMessage('教学经历更新','教学经历更新','教师端');
        }
        if (SystemMessage::where('action',3)->value('text_message') == 1) {
            $admin_mobile = SystemMessage::where('action',3)->value('admin_mobile');
            // 发送短信
            $easySms = new EasySms($config);
            try {
                $admin_number = new PhoneNumber($admin_mobile);
                $text = '教学经历';
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
     * 经历列表
     * @return \Illuminate\Http\JsonResponse
     */
    public function experience_list()
    {
        $user_id = Auth::id();
        $result = TeacherCareer::where('user_id',$user_id)->orderBy('updated_at','desc')->get();
        foreach ($result as $v) {
            $v->subject = explode(',',$v['subject']);
            $v->object = explode(',',$v['object']);
            $v->teaching_type = explode(',',$v['teaching_type']);
        }
        return $this->success('经历列表',$result);
    }

    /**
     * 删除经历
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete_experience()
    {
        $data = \request()->all();
        $id = $data['id'] ?? 0;
        $experience_info = TeacherCareer::find($id);
        if (!$experience_info) {
            return $this->error('经历不存在');
        }
        // 当前用户id
        $user_id = Auth::id();
        if($experience_info->user_id !== $user_id) {
            return $this->error('无权删除');
        }
        $result = $experience_info->delete();
        if (!$result) {
            return $this->error('删除失败');
        }
        return $this->success('删除成功');
    }

    public function my_team()
    {

    }

    /**
     * 我的收藏
     * @return \Illuminate\Http\JsonResponse
     */
    public function collection()
    {
        $data = \request()->all();
        $page_size = $data['page_size'] ?? 10;
        $type = $data['type'] ?? 1;
        $longitude = $data['longitude'] ?? 0;
        $latitude = $data['latitude'] ?? 0;
        // 当前用户
        $user = Auth::user();
        if ($type == 1) {
            $result = Collect::with(['teacher','teacher_info','teacher_career','teacher_education'])->where(['user_id' => $user->id,'type' => $type])->paginate($page_size);
            foreach ($result as $v) {
                $subject = [];
                foreach ($v->teacher_career as $vv) {
                    // 课程
                    $subject[] = explode('、',$vv->subject);
                }
                $v->subject = array_values(array_unique(array_reduce($subject,'array_merge',[])));
                if (isset($v->teacher_info)) {
                    $v->teacher->picture = $v->teacher_info->picture;
                }
                $v->is_pay = UserTeacherOrder::where(['user_id' => $user->id,'teacher_id' => $v->teacher_id,'status' => 1])->exists();
            }
        } else {
            $result = Collect::with(['course'])->where(['user_id' => $user->id,'type' => $type])->paginate($page_size);
            foreach ($result as $v) {
                $course_info = Course::find($v->course->id);
                if ($course_info->adder_role == 4) {
                    $v->distance = calculate_distance($latitude,$longitude,$v->course->organization->latitude,$v->course->organization->longitude);
                    $v->course_organ = $v->course->organization;
                }
                $v->is_entry = UserCourse::where(['user_id' => $user->id,'course_id' => $course_info->id])->exists();
            }
        }

        return $this->success('我的收藏',$result);
    }

    /**
     * 我的钱包
     * @return \Illuminate\Http\JsonResponse
     */
    public function bill()
    {
        $data = \request()->all();
        $is_all = $data['is_all'] ?? 0;
        $page_size = $data['page_size'] ?? 10;
        // 当前用户
        $user = Auth::user();
        if ($is_all) {
            // 筛选
            $where = [];
            if (isset($data['in_or_out'])) {
                $condition = $data['in_or_out'] == 0 ? '>' : '<';
                $where[] = ['amount',$condition,0];
            }
            if (isset($data['type'])) {
                $where[] = [function ($query) {
                    $query->where('type',3)
                        ->orWhere('type',9);
                }];
            }
            if (isset($data['created_at'])) {
                $where[] = ['created_at','>=',$data['created_at'].' 00:00:00'];
                $where[] = ['created_at','<=',$data['created_at'].' 23:59:59'];
            }
            $result = Bill::with('user')->where('user_id',$user->id)->where($where)->orderByDesc('created_at')->paginate($page_size);
        } else {
            $result = Bill::with('user')->where('user_id',$user->id)->orderByDesc('created_at')->limit(10)->get();
        }
        // 我的收益
        $total_income = $user->total_income;
        // 可提现余额
        $withdraw_balance = $user->withdraw_balance;
        return $this->success('我的钱包',compact('result','total_income','withdraw_balance'));
    }

    /**
     * 申请提现
     * @return \Illuminate\Http\JsonResponse
     */
    public function withdraw()
    {
        $config = config('services.sms');
        $data = \request()->all();
        $rules = [
            'amount' => 'required|integer',
            'account' => 'required'
        ];
        $messages = [
            'amount.required' => '金额不能为空',
            'amount.integer' => '金额只能为正整数',
            'account.required' => '账号不能为空'
        ];
        $validator = Validator::make($data,$rules,$messages);
        if ($validator->fails()) {
            $errors = $validator->errors();
            return $this->error(implode(',',$errors->all()));
        }
        // 当前用户
        $user = Auth::user();
        if ($data['amount'] > $user->withdraw_balance) {
            return $this->error('可提现余额不足');
        }
        // 查询手续费
        $base_information = BaseInformation::first();
        $withdraw_min = $base_information->withdraw_min;
        $withdraw_commission = $base_information->withdraw_commission;
        if ($data['amount'] < $withdraw_min) {
            return $this->error('最少提现'.$withdraw_min);
        }
        if ($data['amount'] < $user->balance - $withdraw_commission) {
            return $this->error('余额不足');
        }
        // 提现记录
        $withdraw_data = [
            'user_id' => $user->id,
            'role' => $user->role,
            'mobile' => $data['mobile'] ?? '',
            'amount' => $data['amount'] - $withdraw_commission,
            'type' => $data['type'],
            'username' => $data['username'],
            'account' => $data['account'],
            'status' => 0,
            'created_at' => Carbon::now()
        ];
        if ($data['type'] == 3) {
            $withdraw_data['bank'] = $data['bank'] ?? '';
        }
        // 账单记录
        $bill_data = [
            'user_id' => $user->id,
            'amount' => '-'.$data['amount'],
            'type' => 1,
            'status' => 0,
            'created_at' => Carbon::now()
        ];
        DB::transaction(function () use ($withdraw_data,$bill_data,$user,$data) {
            // 写入提现表
            DB::table('withdraw')->insert($withdraw_data);
            // 写入账单
            DB::table('bills')->insert($bill_data);
            // 更新余额
            DB::table('users')->where('id',$user->id)->decrement('withdraw_balance',$data['amount']);
        });
        // 发送通知
        if (SystemMessage::where('action',11)->value('site_message') == 1) {
            (new PlatformMessage())->saveMessage('申请提现','申请提现','用户端');
        }
        if (SystemMessage::where('action',11)->value('text_message') == 1) {
            $mobile = SystemMessage::where('action',11)->value('admin_mobile');
            // 发送短信
            $easySms = new EasySms($config);
            try {
                $number = new PhoneNumber($mobile);
                $easySms->send($number,[
                    'content'  => "【添添学】有新的提现申请",
                ]);
            } catch (Exception|NoGatewayAvailableException $exception) {
                return $this->error($exception->getResults());
            }
        }
        return $this->success('申请成功');
    }

    /**
     * 更换手机号
     * @return \Illuminate\Http\JsonResponse
     */
    public function update_mobile()
    {
        $data = \request()->all();
        $rules = [
            'mobile' => 'required|phone_number'
        ];
        $messages = [
            'mobile.required' => '手机号不能为空',
            'mobile.phone_number' => '手机号格式错误'
        ];
        $validator = Validator::make($data,$rules,$messages);
        if ($validator->fails()) {
            $errors = $validator->errors();
            return $this->error(implode(',',$errors->all()));
        }
        // 当前用户
        $user = Auth::user();
        // 判断手机号是否已经被使用
        $is_user = User::where('mobile',$data['mobile'])->first();
        if ($is_user) {
            return $this->error('该手机号已被注册');
        }
        // 更新手机号
        $user->mobile = $data['mobile'];
        $user->save();
        return $this->success('绑定成功');
    }

    /**
     * 检查教师资料
     * @return \Illuminate\Http\JsonResponse
     */
    public function check_teacher_info()
    {
        // 当前用户
        $user = Auth::user();
        // 查询资料
        if (!$user->is_real_auth || !TeacherInfo::where('user_id',$user->id)->exists() || !TeacherCareer::where('user_id',$user->id)->exists()) {
            return $this->error('资料不完善');
        }
        return $this->success('资料完善');
    }

    /**
     * 投递
     * @return \Illuminate\Http\JsonResponse
     */
    public function deliver()
    {
        $config = config('services.sms');
        $data = \request()->all();
        $rules = [
            'course_id' => 'required'
        ];
        $messages = [
            'course_id.required' => '课程不能为空',
        ];
        $validator = Validator::make($data,$rules,$messages);
        if ($validator->fails()) {
            $errors = $validator->errors();
            return $this->error(implode(',',$errors->all()));
        }
        // 查询课程
        $course_info = Course::find($data['course_id']);
        if (!$course_info) {
            return $this->error('课程不存在');
        }
        if ($course_info->course_status == 4) {
            return $this->error('该课程已被授权');
        }
        // 当前用户
        $user = Auth::user();
        $out_trade_no = app('snowflake')->id();
        $adder_field = $course_info->adder_role == 1 ? 'parent_id' : 'organ_id';
        if ($course_info->adder_role == 2) {
            $type = 2;
        } else if ($course_info->adder_role == 4) {
            $type = 5;
        } else {
            $type = 4;
        }
        // 金额
        $amount = get_service_price($type,$user->city_id,$user->province_id,$user->district_id);
        // $amount = 0.01;
        // 查看是否已投递
        $deliver_data = [
            'user_id' => $user->id,
            'course_id' => $data['course_id'],
            'status' => 0,
            $adder_field => $course_info->adder_id,
            'introduce' => $data['introduce'] ?? '',
            'image' => $data['image'] ?? '',
            'out_trade_no' => $out_trade_no,
            'pay_status' => 0,
            'amount' => $amount,
            'created_at' => Carbon::now()
        ];
        // 保存数据
        $result = DeliverLog::updateOrCreate(['user_id' => $user->id,'course_id' => $data['course_id']],$deliver_data);
        $course_info->deliver_number += 1;
        $course_info->entry_number += 1;
        $course_info->course_status = 1;
        $course_info->update();
        if (!$result) {
            return $this->error('投递失败');
        }
        // 发送通知
        if (SystemMessage::where('action',9)->value('site_message') == 1) {
            // (new PlatformMessage())->saveMessage('教师投递','教师投递','教师端');
            (new Message())->saveMessage($course_info->adder_id,$user->id,'教师投递','有教师投递您的需求',$data['course_id'],1,6);
        }
        if (SystemMessage::where('action',9)->value('text_message') == 1) {
            $adder_mobile = User::where('id',$course_info->adder_id)->value('mobile');
            // 发送短信
            $easySms = new EasySms($config);
            $adder_number = new PhoneNumber($adder_mobile);
            $easySms->send($adder_number,[
                'content'  => "【添添学】有新的投递",
            ]);
        }
        return $this->success('投递成功',compact('out_trade_no','amount'));
    }

    /**
     * 实名认证
     * @return \Illuminate\Http\JsonResponse
     */
    public function real_auth()
    {
        $config = config('services.sms');
        $data = \request()->all();
        $rules = [
            'id_card_front' => 'required',
            'id_card_backend' => 'required',
            'picture' => 'required'
        ];
        $messages = [
            'id_card_front.required' => '身份证正面照片不能为空',
            'id_card_backend.required' => '身份证背面照片不能为空',
            'picture.required' => '个人照片不能为空'
        ];
        $validator = Validator::make($data,$rules,$messages);
        if ($validator->fails()) {
            $errors = $validator->errors();
            return $this->error(implode(',',$errors->all()));
        }
        // 当前用户
        $user = Auth::user();
        $auth_data = [
            'user_id' => $user->id,
            'id_card_front' => $data['id_card_front'],
            'id_card_backend' => $data['id_card_backend'],
            'picture' => $data['picture'],
            'status' => 0
        ];
        $result = TeacherInfo::updateOrCreate(['user_id' => $user->id],$auth_data);
        if (!$result) {
            return $this->error('提交失败');
        }
        // 给平台发送消息
        if (SystemMessage::where('action',5)->value('site_message') == 1) {
            (new PlatformMessage())->saveMessage('教师实名认证',$user->name.'教师实名认证','教师端');
        }
        if (SystemMessage::where('action',3)->value('text_message') == 1) {
            $admin_mobile = SystemMessage::where('action',3)->value('admin_mobile');
            // 发送短信
            $easySms = new EasySms($config);
            try {
                $admin_number = new PhoneNumber($admin_mobile);
                $text = '实名认证';
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
     * 获取实名认证结果&教育经历审核结果
     * @return \Illuminate\Http\JsonResponse
     */
    public function get_verify_result()
    {
        $data = \request()->all();
        $type = $data['type'] ?? 1;
        if ($type == 1) {
            $condition = 'is_real_auth';
            $reason_field = 'real_auth_reason';
        } else {
            $condition = 'is_education';
            $reason_field = 'education_reason';
        }
        // 当前用户
        $user = Auth::user();
        // 查询数据
        if (!$user->$condition) {
            // 失败原因
            $reason = $user->teacher_info->$reason_field;
            return $this->error('审核未通过',compact('reason'));
        }
        // 返回教师信息
        $info = $user->teacher_info;
        return $this->success('审核成功',$info);
    }

    /**
     * 教育经历
     * @return \Illuminate\Http\JsonResponse
     */
    public function update_education()
    {
        $config = config('services.sms');
        $data = \request()->all();
        $rules = [
            'highest_education' => 'required',
            'education_id' => 'required',
            'graduate_school' => 'required',
            'speciality' => 'required',
            'graduate_cert' => 'required',
            'diploma' => 'required',
        ];
        $messages = [
            'highest_education.required' => '学历不能为空',
            'education_id.required' => '学历id不能为空',
            'graduate_school.required' => '毕业院校不能为空',
            'speciality.required' => '专业不能为空',
            'graduate_cert.required' => '毕业证书不能为空',
            'diploma.required' => '学位证书不能为空',
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
            'highest_education' => $data['highest_education'],
            'education_id' => $data['education_id'],
            'graduate_school' => $data['graduate_school'],
            'speciality' => $data['speciality'],
            'graduate_cert' => $data['graduate_cert'],
            'diploma' => $data['diploma'],
            'status' => 0
        ];
        $result = TeacherEducation::updateOrCreate(['user_id' => $user->id],$education_data);
        if (!$result) {
            return $this->error('提交失败');
        }
        // 发送通知
        if (SystemMessage::where('action',3)->value('site_message') == 1) {
            (new PlatformMessage())->saveMessage('教育经历更新','教育经历更新','教师端');
        }
        if (SystemMessage::where('action',3)->value('text_message') == 1) {
            $admin_mobile = SystemMessage::where('action',3)->value('admin_mobile');
            // 发送短信
            $easySms = new EasySms($config);
            try {
                $admin_number = new PhoneNumber($admin_mobile);
                $text = '教育经历';
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
     * 设置标签
     * @return \Illuminate\Http\JsonResponse
     */
    public function setting_tags()
    {
        $data = \request()->all();
        // 当前用户
        $user = Auth::user();
        $tag_data = [];
        foreach ($data['tag_name'] as $v) {
            $tag_data[] = [
                'user_id' => $user->id,
                'tag' => $v,
                'created_at' => Carbon::now()
            ];
        }
        DB::table('teacher_tags')->insert($tag_data);
        return $this->success('设置成功');
    }

    /**
     * 教学经历详情
     * @return \Illuminate\Http\JsonResponse
     */
    public function career_detail()
    {
        $data = \request()->all();
        $id = $data['id'] ?? 0;
        // 查询经历
        $career_info = TeacherCareer::find($id);
        if (!$career_info) {
            return $this->error('经历不存在');
        }
        // 当前用户
        $user = Auth::user();
        if ($user->id !== $career_info->user_id) {
            return $this->error('错误请求');
        }
        $career_info->object = explode(',',$career_info->object);
        $career_info->subject = explode(',',$career_info->subject);
        $career_info->teaching_type = explode(',',$career_info->teaching_type);
        return $this->success('教学经历',$career_info);
    }

    /**
     * 我的报名
     * @return \Illuminate\Http\JsonResponse
     */
    public function my_entry()
    {
        $data = \request()->all();
        Log::info('data: ',$data);
        $page_size = $data['page_size'] ?? 10;
        $date_sort = $data['date_sort'] ?? 'desc';
        $entry_start_date = $data['entry_start_date'] ?? Carbon::createFromTimestamp(0)->format('Y-m-d');
        $entry_end_date = $data['entry_end_date'] ?? Carbon::now()->format('Y-m-d');
        $longitude = $data['longitude'] ?? 0;
        $latitude = $data['latitude'] ?? 0;
        $distance_min = $data['distance_min'] ?? 0;
        $distance_max = $data['distance_max'] ?? 0;
        // 当前用户
        $user = Auth::user();
        // $user = User::find(1);
        $where = [];
        if (isset($data['type'])) {
            $where[] = ['type','=',$data['type']];
        }
        if (isset($data['name'])) {
            $where[] = ['name','like','%'.$data['name'].'%'];
        }
        if (isset($data['subject'])) {
            $where[] = ['subject','=',$data['subject']];
        }
        if (isset($data['method'])) {
            $where[] = ['method','=',$data['method']];
        }
        if (isset($data['class_price_min']) && isset($data['class_price_max'])) {
            $where[] = ['class_price','>=',$data['class_price_min']];
            $where[] = ['class_price','<=',$data['class_price_max']];
        }
        // 查询我报名的课程
        if (isset($data['distance_min']) && isset($data['distance_max'])) {
            $longitude = $data['longitude'];
            $latitude = $data['latitude'];
            $course = $user->user_courses()->where($where)->whereHas('organization',function ($query) use ($longitude,$latitude,$distance_min,$distance_max) {
                $query->select(['id', 'name'])
                    ->selectRaw("(6371 * acos(cos(radians($latitude)) * cos(radians(latitude)) *
                    cos(radians(longitude) - radians($longitude)) + sin(radians($latitude)) *
                    sin(radians(latitude)))) AS distance")
                    ->having('distance', '>=', $distance_min)
                    ->having('distance', '<=', $distance_max)
                    ->orderBy('distance', 'asc');
            })->wherePivotBetween('created_at',[$entry_start_date." 00:00:00",$entry_end_date." 23:59:59"])->orderByPivot('created_at',$date_sort)->paginate($page_size);
        } else {
            $course = $user->user_courses()->where($where)->wherePivotBetween('created_at',[$entry_start_date." 00:00:00",$entry_end_date." 23:59:59"])->orderByPivot('created_at',$date_sort)->paginate($page_size);
        }

        foreach ($course as $v) {
            $v->is_expire = Carbon::now() > $v->end_time ? 1 : 0;
            $v->entry_time = Carbon::parse($v->pivot->created_at)->format('Y-m-d H:i:s');
            $v->organization_name = $v->organization->name;
            $v->distance = calculate_distance($latitude,$longitude,$v->organization->latitude,$v->organization->longitude);
        }
        return $this->success('我的报名',$course);
    }

    /**
     * 邀请码
     * @return \Illuminate\Http\JsonResponse
     */
    public function invite_code()
    {
        $user = Auth::user();
        $file_name = "/qr_code/" . $user->id . '.jpg';
        if (!file_exists(public_path() . $file_name)) {
            $access_token = get_access_token();
            $request_data = [
                'page' => 'pages/login/index',
                'scene' =>  $user->id,
                'check_path' => true,
                'env_version' => 'release'
            ];

            $request_url = 'https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token='.$access_token;
            $result = Http::post($request_url,$request_data);
            // dd($result);

            $file = public_path()."/qr_code/".$user->id.".jpg";
            file_put_contents($file, $result);
            // 保存
            $user->invite_qrcode = env('APP_URL').$file_name;
            $user->update();
            return $this->success('邀请码',env('APP_URL').$file_name);
        }
        return $this->success('邀请码',env('APP_URL').$file_name);
    }

    /**
     * 删除用户
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete_student()
    {
        $data = \request()->all();
        $id = $data['id'] ?? 0;
        $info = ParentStudent::find($id);
        if (!$info) {
            return $this->error('学生不存在');
        }
        // 当前用户
        $user = Auth::user();
        // 查询是否为当前家长的学生
        if ($user->id !== $info->user_id) {
            return $this->error('数据错误');
        }
        $info->delete();
        return $this->success('删除成功');
    }

    /**
     * 我的教师
     * @return \Illuminate\Http\JsonResponse
     */
    public function my_teachers()
    {
        $data = \request()->all();
        $page_size = $data['page_size'] ?? 10;
        $page = $data['page'] ?? 1;
        // 当前用户
        $user = Auth::user();
        if ($user->role == 2) {
            $deliver_teachers = DeliverLog::with(['teacher_info','teacher_experience','teacher_detail','teacher_education'])->where(['parent_id' => $user->id,'pay_status' => 1])->get();
            $buy_teachers = UserTeacherOrder::with(['teacher_info','teacher_experience','teacher_detail','teacher_education'])->where(['user_id' => $user->id,'status' => 1])->get();
            $merge_teachers = $deliver_teachers->merge($buy_teachers)->unique();
            $teachers = new LengthAwarePaginator(
                $merge_teachers->forPage($page, $page_size),
                $merge_teachers->count(),
                $page_size,
                $page,
                ['path' => LengthAwarePaginator::resolveCurrentPath()]
            );
        } else {
            $teachers = UserTeacherOrder::with(['teacher_info','teacher_experience','teacher_detail'])->where(['user_id' => $user->id,'status' => 1])->paginate($page_size);
        }

        $teaching_year = 0;
        $subject = [];
        foreach ($teachers as $teacher) {
            foreach ($teacher->teacher_experience as $experience) {
                $start_time = Carbon::parse($experience->start_time);
                $end_time = Carbon::parse($experience->end_time);
                $teaching_years = $start_time->diffInYears($end_time);
                $teaching_year += $teaching_years;
                // 课程
                $subject[] = explode(',',$experience->subject);
            }
            $teacher->teaching_year = $teaching_year;
            $teacher->subject = array_values(array_unique(array_reduce($subject,'array_merge',[])));
        }
        $teachers->teaching_year = $teaching_year;
        $teachers->subject = array_values(array_unique(array_reduce($subject,'array_merge',[])));
        return $this->success('我的教师',$teachers);
    }

    /**
     * 消息已读
     * @return \Illuminate\Http\JsonResponse
     */
    public function read_message()
    {
        $data = \request()->all();
        $id = $data['id'] ?? 0;
        $message = Message::find($id);
        if (!$message) {
            return $this->error('消息不存在');
        }
        // 当前用户
        $user = Auth::user();
        if ($message->user_id !== $user->id) {
            return $this->error('数据错误');
        }
        $message->status = 1;
        $message->update();
        return $this->success('消息已读');
    }

    /**
     * 更新免冠照片
     * @return \Illuminate\Http\JsonResponse|void
     */
    public function update_picture()
    {
        $data = \request()->all();
        $rules = [
            'picture' => 'required',
        ];
        $messages = [
            'picture.required' => '免冠照片不能为空',
        ];
        $validator = Validator::make($data,$rules,$messages);
        if ($validator->fails()) {
            $errors = $validator->errors();
            return $this->error(implode(',',$errors->all()));
        }
        // 当前用户
        $teacher_info = TeacherInfo::where('user_id',Auth::id())->first();
        $teacher_info->picture = $data['picture'];
        $teacher_info->status = 0;
        $teacher_info->update();
        return $this->success('更新成功');
    }
}
