<?php

namespace App\Http\Controllers;

use App\Models\Region;
use App\Models\TeacherCert;
use App\Models\TeacherEducation;
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
            'teacher_cert' => $data['teacher_cert'],
            'honor_cert' => $data['honor_cert']
        ];
        $result = TeacherCert::updateOrCreate(['user_id' => $user->id],$education_data);
        if (!$result) {
            return $this->error('提交失败');
        }
        return $this->success('提交成功');
    }
}
