<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ActivityController extends Controller
{
    /**
     * 活动列表
     * @return \Illuminate\Http\JsonResponse
     */
    public function list()
    {
        $data = \request()->all();
        $page_size = $data['page_size'] ?? 10;
        $status = $data['status'] ?? 1;
        // 当前用户
        $user = Auth::user();
        // 用户角色
        $role = $user->role;
        $role_arr = ['','学生','家长','教师','机构'];
        $role_str = $role_arr[$role];
        $result = DB::table('activities')->whereRaw("FIND_IN_SET('$role_str',object)")->where('status',$status)->paginate($page_size);
        return $this->success('活动列表',$result);
    }

    /**
     * 活动详情
     * @return \Illuminate\Http\JsonResponse
     */
    public function detail()
    {
        $data = \request()->all();
        $id = $data['id'] ?? 0;
        // 当前用户
        $user = Auth::user();
        $result = Activity::find($id);
        if (!$result) {
            return $this->error('活动不存在');
        }
        $arr = ['','student_','parent_','teacher_','organ_'];
        $prefix = $arr[$user->role];
        $result->first_reward = $result->$prefix.'first_reward';
        $result->second_reward = $result->$prefix.'second_reward';
        return $this->success('活动详情',$result);
    }
}
