<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Bill;
use App\Models\Collect;
use App\Models\Course;
use App\Models\DeliverLog;
use App\Models\ParentStudent;
use App\Models\TeacherCareer;
use App\Models\TeacherEducation;
use App\Models\TeacherInfo;
use App\Models\TeacherRealAuth;
use App\Models\TeacherTag;
use App\Models\User;
use App\Models\UserContact;
use App\Models\UserTeacherOrder;
use App\Models\Withdraw;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Validator;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class UserController extends Controller
{
    public function update_info()
    {
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
        $data['number'] = create_user_number($data['city_id'],$user->id);
        if ($user->role != 3) {
            $data['age'] = Carbon::parse($data['birthday'])->diffInYears(Carbon::now());
        }
        $data['created_at'] = Carbon::now();
        $result = DB::table('users')->where('id',$user->id)->update($data);
        if (!$result) {
            return $this->error('更新失败');
        }
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
        $result = UserContact::updateOrCreate(['user_id' => $user_id,'relation' => $data['relation']],$data);
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
        $data['user_id'] = $user_id;
        // 查询是否存在
        if (TeacherInfo::where('user_id',$user_id)->exists()) {
            $data['updated_at'] = Carbon::now();
            $result = DB::table('teacher_info')->where('user_id',$user_id)->update($data);
        } else {
            $data['created_at'] = Carbon::now();
            $result = DB::table('teacher_info')->insert($data);
        }
        if (!$result) {
            return $this->error('提交失败');
        }
        return $this->success('提交成功');
    }

    /**
     * 教学经历-教师
     * @return \Illuminate\Http\JsonResponse
     */
    public function teaching_experience()
    {
        $data = \request()->all();
        $id = $data['id'] ?? 0;
        $user_id = Auth::id();
        $data['user_id'] = $user_id;
        // 查询是否存在
        $data['created_at'] = Carbon::now();
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
            $result = Collect::with(['teacher','teacher_info','teacher_career'])->where(['user_id' => $user->id,'type' => $type])->paginate($page_size);
            foreach ($result as $v) {
                $subject = [];
                foreach ($v->teacher_career as $vv) {
                    // 课程
                    $subject[] = explode('、',$vv->subject);
                }
                $v->subject = array_values(array_unique(array_reduce($subject,'array_merge',[])));
            }
        } else {
            $result = Collect::with(['course'])->where(['user_id' => $user->id,'type' => $type])->paginate($page_size);
            foreach ($result as $v) {
                $v->distance = calculate_distance($latitude,$longitude,$v->course->organization->latitude,$v->course->organization->longitude);
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
                $where[] = ['type','=',$data['type']];
            }
            if (isset($data['created_at'])) {
                $where[] = ['created_at','>=',$data['created_at'].' 00:00:00'];
                $where[] = ['created_at','<=',$data['created_at'].' 23:59:59'];
            }
            $result = Bill::where('user_id',$user->id)->where($where)->paginate($page_size);
        } else {
            $result = Bill::where('user_id',$user->id)->limit(10)->get();
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
        // 提现记录
        $withdraw_data = [
            'user_id' => $user->id,
            'amount' => $data['amount'],
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
        $data = \request()->all();
        $rules = [
            'introduce' => 'required',
            'course_id' => 'required'
        ];
        $messages = [
            'introduce.required' => '个人介绍不能为空',
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
        // 当前用户
        $user = Auth::user();
        // 查看是否已投递
        $deliver_data = [
            'user_id' => $user->id,
            'course_id' => $data['course_id'],
            'status' => 0,
            'introduce' => '个人介绍',
            'image' => $data['image'] ?? '',
            'created_at' => Carbon::now()
        ];
        // 保存数据
        $result = DeliverLog::updateOrCreate(['user_id' => $user->id,'course_id' => $data['course_id']],$deliver_data);
        if (!$result) {
            return $this->error('投递失败');
        }
        return $this->success('投递成功');
    }

    /**
     * 实名认证
     * @return \Illuminate\Http\JsonResponse
     */
    public function real_auth()
    {
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
            'id_card_backend' => $data['id_card_backend']
        ];
        $result = TeacherRealAuth::updateOrCreate(['user_id' => $user->id],$auth_data);
        if (!$result) {
            return $this->error('提交失败');
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
            'diploma' => $data['diploma']
        ];
        $result = TeacherEducation::updateOrCreate(['user_id' => $user->id],$education_data);
        if (!$result) {
            return $this->error('提交失败');
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
        DB::transaction(function () use ($user, $tag_data) {
            DB::table('teacher_tags')->where('user_id',$user->id)->delete();
            DB::table('teacher_tags')->insert($tag_data);
        });
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
        $career_info->object = explode('、',$career_info->object);
        $career_info->subject = explode('、',$career_info->subject);
        $career_info->teaching_type = explode('、',$career_info->teaching_type);
        return $this->success('教学经历',$career_info);
    }

    /**
     * 我的报名
     * @return \Illuminate\Http\JsonResponse
     */
    public function my_entry()
    {
        $data = \request()->all();
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
        $where = [];
        if (isset($data['type'])) {
            $where[] = ['type','=',$data['type']];
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
        $course = $user->user_courses()->where($where)->whereHas('organization',function ($query) use ($longitude,$latitude,$distance_min,$distance_max) {
            $query->select(['id', 'name'])
                ->selectRaw("(6371 * acos(cos(radians($latitude)) * cos(radians(latitude)) *
                    cos(radians(longitude) - radians($longitude)) + sin(radians($latitude)) *
                    sin(radians(latitude)))) AS distance")
                ->having('distance', '>=', $distance_min)
                ->having('distance', '<=', $distance_max)
                ->orderBy('distance', 'asc');
        })->wherePivotBetween('created_at',[$entry_start_date." 00:00:00",$entry_end_date." 23:59:59"])->orderByPivot('created_at',$date_sort)->paginate($page_size);
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
        // 当前用户
        $user = Auth::user();
        $path = 'pages/login/index?id='.$user->id;
        $access_token = get_access_token();
        // dd($access_token);
        $url = 'https://api.weixin.qq.com/cgi-bin/wxaapp/createwxaqrcode?access_token='.$access_token;
        $result = Http::post($url,[
            'path' => $path,
            'width' => 300
        ])->body();
        /*dd($result);
        $info = json_decode($result,true);
        dd($info);*/
        return $this->success('邀请码',$result);
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
        // 当前用户
        $user = Auth::user();
        $teachers = UserTeacherOrder::with(['teacher_info','teacher_experience','teacher_detail'])->where(['user_id' => $user->id,'status' => 1])->paginate($page_size);
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
}
