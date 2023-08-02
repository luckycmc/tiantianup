<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class TeacherController extends Controller
{
    /**
     * 教师列表
     * @return \Illuminate\Http\JsonResponse
     */
    public function list()
    {
        $data = \request()->all();
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
        if (isset($data['gender'])) {
            $where[] = ['users.gender','=',$data['gender']];
        }
        if (isset($data['fillter_teaching_year'])) {
            $where[] = ['teacher_info.teaching_year','>',$data['fillter_teaching_year'][0]];
            $where[] = ['teacher_info.teaching_year','<',$data['fillter_teaching_year'][1]];
        }
        // dd($where);
        if (isset($data['filter_is_auth'])) {
            $where[] = ['users.is_real_auth','=',$data['is_real_auth']];
        }
        $result = User::leftJoin('teacher_info', 'users.id', '=', 'teacher_info.user_id')
            ->leftJoin('teacher_career','users.id','=','teacher_career.user_id')
            ->where($where)
            ->orderBy($sort_field,$order)
            ->select('users.*','teacher_info.highest_education','teacher_info.graduate_school','teacher_info.teaching_year','teacher_career.subject')
            ->paginate($page_size);
        foreach ($result as $v) {
            // 科目
            $v->subject = explode(',',$v->subject);
        }
        return $this->success('教师列表',$result);
    }
}
