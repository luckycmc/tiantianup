<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        if (in_array($role,[1,2])) {
            $object = 1;
        } else if ($role == 3) {
            $object = 2;
        } else {
            $object = 3;
        }
        $result = Activity::where(['status' => $status,'object' => 0])->orWhere(['status' => $status,'object' => $object])->paginate($page_size);
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
        $result = Activity::with('reward')->find($id);
        if (!$result) {
            return $this->error('活动不存在');
        }
        return $this->success('活动详情',$result);
    }
}
