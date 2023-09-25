<?php

namespace App\Http\Controllers;

use App\Models\Region;
use App\Models\TeacherCert;
use App\Models\TeacherEducation;
use App\Models\TeacherImage;
use App\Models\TeacherRealAuth;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        // dd($data);
        $page_size = $data['page_size'] ?? 10;
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
        // dd($where);
        if (isset($data['filter_is_auth'])) {
            $where[] = ['users.is_real_auth','=',$data['is_real_auth']];
        }
        $result = User::leftJoin('teacher_info', 'users.id', '=', 'teacher_info.user_id')
            ->leftJoin('teacher_career','users.id','=','teacher_career.user_id')
            ->where($where)
            ->where(['district_id' => $district_id])
            ->orderBy($sort_field,$order)
            ->select('users.*','teacher_info.highest_education','teacher_info.graduate_school','teacher_info.teaching_year','teacher_career.subject')
            ->paginate($page_size);
        foreach ($result as $v) {
            // 科目
            $v->subject = explode(',',$v->subject);
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
            'teacher_cert' => implode(',',$data['teacher_cert']),
            'other_cert' => implode(',',$data['other_cert']),
            'honor_cert' => implode(',',$data['honor_cert']),
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
        $arr = [new TeacherRealAuth(),new TeacherEducation(),new TeacherCert(),new TeacherImage()];
        // 查询实名认证
        $result = $arr[$type-1]::where('user_id',$user->id)->first();
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
        foreach ($data['url'] as $v) {
            $insert_data = [
                'user_id' => $user->id,
                'url' => $v,
                'status' => 0
            ];
            $result = TeacherImage::updateOrCreate(['user_id' => $user->id],$insert_data);
            if (!$result) {
                return $this->error('提交失败');
            }
        }
        return $this->success('提交成功');
    }
}
