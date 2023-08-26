<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserTeacherOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Pay;

class CommonController extends Controller
{
    public function wechat_noify()
    {
        $config = config('pay');
        $pay = Pay::wechat($config);
        Log::info('aa');
        try {
            $data = $pay->callback(); // 是的，验签就这么简单！
            Log::info('Log: '.$data);
            $info = $data['resource']['ciphertext'];
            Log::info('info',$info);
            if ($info['trade_state'] == 'SUCCESS') {
                // 查询订单
                $order = UserTeacherOrder::where('out_trade_no',$info['out_trade_no'])->first();
                // 查询支付类型
                if ($order->pay_type == 1) {
                    // 微信支付
                    // 修改支付状态
                    $order->status = 1;
                    $order->save();
                } else if ($order->pay_type == 2) {
                    // 组合支付
                    $order->status = 1;
                    // 查询用户
                    $user = User::find($order->user_id);
                    $user->withdraw_balance = $user->withdraw_balance - $order->discount;
                    $user->save();
                    $order->save();
                }
            }
        } catch (Exception $e) {
            Log::info($data);
        }
    }
}
