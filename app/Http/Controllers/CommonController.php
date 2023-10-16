<?php

namespace App\Http\Controllers;

use App\Models\BaseInformation;
use App\Models\Course;
use App\Models\DeliverLog;
use App\Models\Organization;
use App\Models\OrganRole;
use App\Models\User;
use App\Models\UserCourse;
use App\Models\UserTeacherOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Yansongda\Pay\Exception\Exception;
use Yansongda\Pay\Pay;

class CommonController extends Controller
{
    public function wechat_notify()
    {
        $config = config('pay');
        $pay = Pay::wechat($config);
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
                // 更新课程状态
                $course = Course::find($order->course_id);
                $course->course_status = 4;
                $course->update();
            }
        } catch (Exception $e) {
            Log::info($data);
        }
    }

    public function teacher_wechat_notify()
    {
        $config = config('pay');
        $pay = Pay::wechat($config);
        try {
            $data = $pay->callback(); // 是的，验签就这么简单！
            $info = $data['resource']['ciphertext'];
            if ($info['trade_state'] == 'SUCCESS') {
                // 查询订单
                $order = DeliverLog::where('out_trade_no',$info['out_trade_no'])->first();
                // 查询支付类型
                if ($order->pay_type == 1) {
                    // 微信支付
                    // 修改支付状态
                    $order->pay_status = 1;
                    $order->save();
                } else if ($order->pay_type == 2) {
                    // 组合支付
                    $order->pay_status = 1;
                    // 查询用户
                    $user = User::find($order->user_id);
                    $user->withdraw_balance = $user->withdraw_balance - $order->discount;
                    $user->save();
                    $order->save();
                }
                // 已授权
                $course = Course::find($order->course_id);
                $course->course_status = 4;
                $course->update();
            }
        } catch (Exception $e) {
            Log::info($data);
        }
    }

    public function organ_wechat_notify()
    {
        $config = config('pay');
        $pay = Pay::wechat($config);
        try {
            $data = $pay->callback(); // 是的，验签就这么简单！
            $info = $data['resource']['ciphertext'];
            if ($info['trade_state'] == 'SUCCESS') {
                // 查询订单
                $orders = UserCourse::where('total_out_trade_no',$info['out_trade_no'])->first();
                if (empty($orders)) {
                    // 单个支付
                    $order = UserCourse::where('out_trade_no',$info['out_trade_no'])->first();
                    $order->status = 1;
                    $order->update();
                } else {
                    $order_arr = UserCourse::where('total_out_trade_no',$info['out_trade_no'])->get();
                    foreach ($order_arr as $order) {
                        $order->status = 1;
                        $order->update();
                    }
                }
            }
        } catch (Exception $e) {
            Log::info($data);
        }
    }

    /**
     * 获取基本信息
     * @return \Illuminate\Http\JsonResponse
     */
    public function get_base_information()
    {
        $result = BaseInformation::first();
        return $this->success('基本信息',$result);
    }

    /**
     * 获取角色
     * @return \Illuminate\Http\JsonResponse
     */
    public function get_roles()
    {
        $result = OrganRole::all();
        return $this->success('角色',$result);
    }

    /**
     * 获取机构名称
     * @return \Illuminate\Http\JsonResponse
     */
    public function get_organ_name()
    {
        $data = \request()->all();
        $parent_id = $data['parent_id'] ?? 0;
        $user = User::find($parent_id);
        if (!$user) {
            return $this->error('邀请人不存在');
        }
        // 获取机构名称
        $organ_name = Organization::where('user_id',$parent_id)->value('name');
        return $this->success('机构名称',compact('organ_name'));
    }
}
