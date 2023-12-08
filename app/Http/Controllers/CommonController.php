<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Agreement;
use App\Models\BaseInformation;
use App\Models\Bill;
use App\Models\Course;
use App\Models\DeliverLog;
use App\Models\Organization;
use App\Models\OrganRole;
use App\Models\User;
use App\Models\UserCourse;
use App\Models\UserTeacherOrder;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
            $info = $data['resource']['ciphertext'];
            if ($info['trade_state'] == 'SUCCESS') {
                // 查询订单
                $order = UserTeacherOrder::where('out_trade_no',$info['out_trade_no'])->first();
                // 查询用户
                $user = User::find($order->user_id);
                // 查询支付类型
                if ($order->pay_type == 1) {
                    // 微信支付
                    // 修改支付状态
                    $order->status = 1;
                    $order->save();
                } else if ($order->pay_type == 2) {
                    // 组合支付
                    $order->status = 1;
                    $user->withdraw_balance = $user->withdraw_balance - $order->discount;
                    $user->save();
                    $order->save();
                }
                // 保存日志
                $log_data = [
                    'user_id' => $user->id,
                    'amount' => '-'.$order->amount,
                    'type' => 5,
                    'description' => '查看教师',
                    'created_at' => Carbon::now()
                ];
                DB::table('bills')->insert($log_data);
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
                $course = Course::find($order->course_id);
                /*// 已授权
                $order->status = 4;*/
                // 查询用户
                $user = User::find($order->user_id);
                // 查询支付类型
                if ($order->pay_type == 1) {
                    // 微信支付
                    // 修改支付状态
                    $order->pay_status = 1;
                    $order->save();
                } else if ($order->pay_type == 2) {
                    // 组合支付
                    $order->pay_status = 1;
                    $user->withdraw_balance += $user->withdraw_balance - $order->discount;
                    $user->total_income += $user->withdraw_balance - $order->discount;
                    $user->save();
                    $order->save();
                }
                if ($course->adder_role !== 0) {
                    $course->course_status = 4;
                    $course->update();
                }
                // 保存日志
                $log_data = [
                    'user_id' => $user->id,
                    'amount' => '-'.$order->amount,
                    'type' => 4,
                    'description' => '查看需求',
                    'created_at' => Carbon::now()
                ];
                DB::table('bills')->insert($log_data);
                // 当前时间
                $current = Carbon::now()->format('Y-m-d');
                // 查看是否有成交活动
                $deal_activity = Activity::where(['status' => 1,'type' => 3])->where('start_time', '<=', $current)
                    ->where('end_time', '>=', $current)->first();
                if ($deal_activity) {
                    deal_activity_log($user->id,$order->course_id,$deal_activity);
                }
            }
        } catch (Exception $e) {
            Log::info($e->getMessage());
            return $this->error($e->getMessage());
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
                    // 查询用户
                    $user = User::find($order->course->adder_id);
                    $order->status = 1;
                    $order->update();
                    // 保存日志
                    $log_data = [
                        'user_id' => $user->id,
                        'amount' => '-'.$order->amount,
                        'type' => 10,
                        'description' => '查看报名',
                        'created_at' => Carbon::now()
                    ];
                    DB::table('bills')->insert($log_data);
                } else {
                    $order_arr = UserCourse::where('total_out_trade_no',$info['out_trade_no'])->get();
                    foreach ($order_arr as $order) {
                        $order->status = 1;
                        $order->update();
                        // 查询用户
                        $user = User::find($order->course->adder_id);
                        // 保存日志
                        $log_data = [
                            'user_id' => $user->id,
                            'amount' => '-'.$order->amount,
                            'type' => 10,
                            'description' => '查看报名',
                            'created_at' => Carbon::now()
                        ];
                        DB::table('bills')->insert($log_data);
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
     * 获取协议
     * @return \Illuminate\Http\JsonResponse
     */
    public function get_policy()
    {
        $result = Agreement::all();
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

    public function test_official_push()
    {
        $open_id = 'o163o4hYbLPE_r8_QJKY5Qj0sMy8';
        $data = [
            'content' => 'test'
        ];
        get_official_openid($open_id);
        // send_official_message($open_id,json_encode($data));
    }

    public function test_price()
    {
        $price = get_service_price(2,'','','昌平区');
        dd($price);
    }

    public function test_long()
    {
        get_long_lat('河南省','郑州市','高新区','须水河西路大正水晶森林');
    }

    public function course_list()
    {
        $data = \request()->all();
        $role = 4;
        $page_size = $data['page_size'] ?? 10;
        $result = Course::where('adder_role', 4)->where('end_time','>',Carbon::now())->orderByDesc('created_at')->paginate($page_size);
        return $this->success('课程列表',$result);
    }

    /**
     * 教师列表
     * @return \Illuminate\Http\JsonResponse
     */
    public function teacher_list()
    {
        $data = \request()->all();
        $page_size = $data['page_size'] ?? 10;
        $result = User::where(['is_perfect' => 1,'status' => 1,'role' => 3,'is_recommend' => 1])->paginate($page_size);
        return $this->success('教师列表',$result);
    }
}
