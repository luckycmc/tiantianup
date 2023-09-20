<?php

namespace App\Http\Controllers;

use App\Models\Withdraw;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WithdrawController extends Controller
{
    /**
     * 提现列表
     * @return \Illuminate\Http\JsonResponse
     */
    public function list()
    {
        $data = \request()->all();
        $page_size = $data['page_size'] ?? 10;
        $where = [];
        if (isset($data['date'])) {
            $where[] = ['created_at','>=',$data['date'].' 00:00:00'];
            $where[] = ['created_at','<=',$data['date'].' 23:59:59'];
        }
        if (isset($data['status'])) {
            $where[] = ['status','=',$data['status']];
        }
        // 当前用户
        $user = Auth::user();
        // 查询记录
        $result = Withdraw::where('user_id',$user->id)->where($where)->orderByDesc('created_at')->paginate($page_size);
        return $this->success('提现列表',$result);
    }
}
