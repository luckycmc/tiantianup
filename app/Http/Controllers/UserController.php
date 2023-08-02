<?php

namespace App\Http\Controllers;

use App\Models\Collect;
use App\Models\ParentStudent;
use App\Models\TeacherCareer;
use App\Models\TeacherInfo;
use App\Models\User;
use App\Models\UserContact;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function update_info()
    {
        $data = \request()->all();
        $role = $data['role'] ?? 1;
        $rules = [
            'avatar' => 'required|url',
            'nickname' => 'required|regex:/^[\p{Han}a-zA-Z]+$/u|max:10',
            'name' => 'required|regex:/^[\p{Han}a-zA-Z]+$/u|max:10',
            'gender' => 'required',
            'province_id' => 'required',
            'city_id' => 'required',
            'district_id' => 'required',
        ];
        $messages = [
            'avatar.required' => '头像不能为空',
            'avatar.url' => '头像格式错误',
            'nickname.required' => '昵称不能为空',
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
        if ($role) {
            $rules['grade'] = 'required';
            $messages['grade.required'] = '年级不能为空';
        }
        $validator = Validator::make($data,$rules,$messages);
        if ($validator->fails()) {
            $error = $validator->errors();
            return $this->error(implode(',',$error->all()));
        }
        $user_id = Auth::id();
        // 用户编号
        $data['number'] = create_user_number($data['city_id'],$user_id);
        $data['age'] = Carbon::parse($data['birthday'])->diffInYears(Carbon::now());
        $data['created_at'] = Carbon::now();
        $result = DB::table('users')->where('id',$user_id)->update($data);
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
        // 查询是否存在
        $contact_info = UserContact::where(['user_id' => $user_id,'name' => $data['name'],'mobile' => $data['mobile'],'relation' => $data['relation']])->first();
        if ($contact_info) {
            return $this->error('联系人已存在');
        }
        $data['user_id'] = Auth::id();
        $data['created_at'] = Carbon::now();
        $result = DB::table('user_contacts')->insert($data);
        if (!$result) {
            return $this->error('添加失败');
        }
        return $this->success('添加成功');
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
        $user_id = Auth::id();
        $data['user_id'] = $user_id;
        // 查询是否存在
        $data['created_at'] = Carbon::now();
        $result = DB::table('teacher_career')->insert($data);
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
                    $subject[] = explode(',',$vv->subject);
                }
                $v->subject = array_values(array_unique(array_reduce($subject,'array_merge',[])));
            }
        } else {
            $result = Collect::with(['course','course_organ'])->where(['user_id' => $user->id,'type' => $type])->paginate($page_size);
            foreach ($result as $v) {
                $v->distance = calculate_distance($data['latitude'],$data['longitude'],$v->course_organ->latitude,$v->course_organ->longitude);
            }
        }

        return $this->success('我的收藏',$result);
    }
}
