<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TeamController extends Controller
{
    /**
     * 我的团队
     * @return \Illuminate\Http\JsonResponse
     */
    public function my_team()
    {
        // 当前用户
        $user = Auth::user();
        // 一级团队
        $child = User::where(['parent_id' => $user->id,'is_perfect' => 1])->select('id','avatar','name','created_at')->get();
        foreach ($child as $v) {
            $v->child_count = User::where('parent_id',$v->id)->count();
        }
        // 二级团队
        $grandson = User::whereIn('parent_id',$child->pluck('id'))->where('is_perfect',1)->get();
        // 一级团队人数
        $child_count = $child->count();
        $grandson_count = $grandson->count();
        $children = $child->take(10);
        return $this->success('我的团队',compact('child_count','grandson_count','children'));
    }

    /**
     * 成员团队
     * @return \Illuminate\Http\JsonResponse
     */
    public function user_team()
    {
        $data = \request()->all();
        $page_size = $data['page_size'] ?? 10;
        $user_id = $data['user_id'] ?? 0;
        if (!User::find($user_id)) {
            return $this->error('用户不存在');
        }
        // 查询一级团队
        $child_list = User::where('parent_id',$user_id)->select('id','name','avatar','created_at')->paginate($page_size);

        foreach ($child_list as $v) {
            $v->child_count = $v->child->count();
        }
        return $this->success('用户团队',$child_list);
    }
}
