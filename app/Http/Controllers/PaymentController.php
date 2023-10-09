<?php

namespace App\Http\Controllers;

use App\Models\DeliverLog;
use App\Models\TeacherCourseOrder;
use App\Models\User;
use App\Models\UserTeacherOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yansongda\Pay\Pay;

class PaymentController extends Controller
{
    public function create_pay_order()
    {
        $config = config('pay');
        // dd($config);
        $data = \request()->all();
        $out_trade_no = $data['out_trade_no'] ?? '';
        $pay_type = $data['pay_type'] ?? 1;
        $role = $data['role'] ?? 0;
        if ($role == 3) {
            $object = new DeliverLog();
        } else {
            $object = new UserTeacherOrder();
        }
        // 查询订单
        $order = $object::where('out_trade_no',$out_trade_no)->first();
        if (!$order) {
            return $this->error('订单不存在');
        }
        // 当前用户
        $user = Auth::user();
        $order->pay_type = $pay_type;
        $order->save();
        // 微信支付
        if ($pay_type == 1) {
            // 调起支付
            $pay_data = [
                'out_trade_no' => $out_trade_no,
                'description' => '服务费',
                'amount' => [
                    'total' => $order->amount * 100,
                    'currency' => 'CNY',
                ],
                'payer' => [
                    'openid' => $user->open_id,
                ]
            ];
            // dd($pay_data);
            $result = Pay::wechat($config)->mini($pay_data);
        } else if ($pay_type == 2) {
            // 组合支付
            // 查询余额
            $balance = $user->withdraw_balance;
            // 更新订单数据
            $order->discount = $balance;
            $order->save();
            // 微信支付金额
            $amount = $order->amount - $balance;
            // 调起支付
            $pay_data = [
                'out_trade_no' => $out_trade_no,
                'description' => '服务费',
                'amount' => [
                    'total' => $amount * 100,
                    'currency' => 'CNY',
                ],
                'payer' => [
                    'openid' => $user->open_id,
                ]
            ];
            $result = Pay::wechat($config)->mini($pay_data);
        } else {
            // 余额支付
            $user->withdraw_balance = $user->withdraw_balance - $order->amount;
            $order->status = 1;
            DB::transaction(function () use ($user,$order) {
                $user->save();
                $order->save();
            });
            $result = '支付成功';
        }
        return $this->success('调起支付',compact('result'));
    }
}
