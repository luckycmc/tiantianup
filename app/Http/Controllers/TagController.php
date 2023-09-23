<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use App\Models\TeacherTag;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TagController extends Controller
{
    /**
     * 标签列表
     * @return \Illuminate\Http\JsonResponse
     */
    public function list()
    {
        $result = Tag::select('name as tag')->get()->toArray();
        // 当前用户
        $user = Auth::user();
        // 查询当前用户标签
        if ($user->role == 3) {
            $tags = TeacherTag::where('user_id',$user->id)->select('tag')->get()->toArray();

            $merge = array_merge($result,$tags);
            $result = collect($merge)->unique()->values()->all();
            foreach ($result as &$v) {
                if (in_array($v,$result) && in_array($v,$tags)) {
                    $v['is_select'] = true;
                } else if (in_array($v,$tags)) {
                    $v['is_select'] = true;
                } else {
                    $v['is_select'] = false;
                }
            }
        }
        return $this->success('标签列表',$result);
    }
}
