<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Course;
use App\Models\DeliverLog;
use App\Models\TeacherCourseOrder;
use App\Models\User;
use App\Models\UserTeacherOrder;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yansongda\Pay\Pay;

class PaymentController extends Controller
{
    public function create_pay_order()
    {
        $config = config('pay');
        $data = \request()->all();
        $out_trade_no = $data['out_trade_no'] ?? '';
        $pay_type = $data['pay_type'] ?? 1;
        $role = $data['role'] ?? 0;
        $pay_config = 'default';
        $type = 5;
        $description = '查看教师';
        if ($role == 3) {
            $object = new DeliverLog();
            $pay_config = 'teacher';
            $status_field = 'pay_status';
            $type = 4;
            $description = '查看需求';
        } else {
            $object = new UserTeacherOrder();
            $status_field = 'status';
        }
        // 查询订单
        $order = $object::where('out_trade_no',$out_trade_no)->first();
        if (!$order) {
            return $this->error('订单不存在');
        }
        if ($order->$status_field == 1) {
            return $this->error('该订单已被支付');
        }
        // 当前用户
        $user = Auth::user();
        if ($user->role == 3) {
            $course_info = Course::find($order->course_id);
            if ($course_info->adder_role == 0) {
                $type = 11;
                $description = '查看代发单';
            }
            if (in_array($course_info->course_status,[4,5])) {
                return $this->error('该订单已关闭');
            }
        }
        $order->pay_type = $pay_type;
        $order->update();
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
                ],
                '_config' => $pay_config,
            ];
            $result = Pay::wechat($config)->mini($pay_data);
        } else if ($pay_type == 2) {
            // 组合支付
            // 查询余额
            $balance = $user->withdraw_balance;
            // 更新订单数据
            $order->discount = $balance;
            $order->update();
            // 微信支付金额
            $amount = bcsub($order->amount,$balance,2);
            Log::info('amount: '.$amount);
            // 调起支付
            $pay_data = [
                'out_trade_no' => $out_trade_no,
                'description' => '服务费',
                'amount' => [
                    'total' => floor($amount*100),
                    'currency' => 'CNY',
                ],
                'payer' => [
                    'openid' => $user->open_id,
                ],
                '_config' => $pay_config,
            ];
            Log::info('pay_status: ',$pay_data);
            $result = Pay::wechat($config)->mini($pay_data);
            /*$user->withdraw_balance = $user->withdraw_balance - $balance;
            $user->update();*/
            // 保存日志
            /*$log_data = [
                'user_id' => $user->id,
                'amount' => '-'.$balance,
                'type' => $type,
                'description' => $description,
                'created_at' => Carbon::now()
            ];
            DB::table('bills')->insert($log_data);*/
            // 当前时间
            $current = Carbon::now()->format('Y-m-d');
            $course_info = Course::find($order->course_id);
            if ($user->role == 3 && $course_info->adder_role !== 0) {
                // 查看是否有成交活动
                $deal_activity = Activity::where(['status' => 1,'type' => 3])->where('start_time', '<=', $current)
                    ->where('end_time', '>=', $current)->first();
                if ($deal_activity) {
                    deal_activity_log($user->id,$order->course_id,$deal_activity);
                }
            }
        } else {
            // 余额支付
            $user->withdraw_balance = $user->withdraw_balance - $order->amount;
            $order->$status_field = 1;
            if ($role == 3) {
                $order->pay_status == 1;
                $order->pay_type == 0;
                $order->update();
            }
            DB::transaction(function () use ($user,$order) {
                $user->update();
                $order->update();
            });
            $result = '支付成功';
            // 保存日志
            $log_data = [
                'user_id' => $user->id,
                'amount' => '-'.$order->amount,
                'type' => $type,
                'discount' => $order->amount,
                'description' => $description,
                'created_at' => Carbon::now()
            ];
            DB::table('bills')->insert($log_data);
            // 当前时间
            $current = Carbon::now()->format('Y-m-d');
            $course_info = Course::find($order->course_id);
            if ($course_info) {
                $course_info->entry_number++;
                $course_info->update();
            }
            if ($user->role == 3 && $course_info->adder_role !== 0) {
                // 查看是否有成交活动
                $deal_activity = Activity::where(['status' => 1,'type' => 3])->where('start_time', '<=', $current)
                    ->where('end_time', '>=', $current)->first();
                if ($deal_activity) {
                    deal_activity_log($user->id,$order->course_id,$deal_activity);
                }
            }
        }
        return $this->success('调起支付',compact('result'));
    }
}
