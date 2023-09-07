<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BillController extends Controller
{
    /**
     * 佣金明细
     * @return \Illuminate\Http\JsonResponse
     */
    public function commission()
    {
        $data = \request()->all();
        $page_size = $data['page_size'] ?? 10;
        // 当前用户
        $user = Auth::user();
        $where = [];
        if (isset($data['type'])) {
            $where[] = ['type','=',$data['type']];
        }
        if (isset($data['date'])) {
            $where[] = ['created_at','>=',$data['date'].' 00:00:00'];
            $where[] = ['created_at','<=',$data['date'].' 23:59:59'];
        }
        // 查询佣金明细
        $result = Bill::where(['user_id' => $user->id,['amount','>',0]])->where($where)->paginate($page_size);
        return $this->success('佣金明细',$result);
    }
}
