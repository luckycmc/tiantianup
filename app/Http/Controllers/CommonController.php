<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Agreement;
use App\Models\Banner;
use App\Models\BaseInformation;
use App\Models\Bill;
use App\Models\Course;
use App\Models\DeliverLog;
use App\Models\Organization;
use App\Models\OrganRole;
use App\Models\Region;
use App\Models\RotateImage;
use App\Models\User;
use App\Models\UserCourse;
use App\Models\UserTeacherOrder;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
                    $user->withdraw_balance -= $order->discount;
                    $user->update();
                    $order->update();
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
                $course->buyer_count += 1;
                $course->update();
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
                    $user->withdraw_balance = $user->withdraw_balance - $order->discount;
                    // $user->total_income = $user->withdraw_balance - $order->discount;
                    $user->update();
                    $order->update();
                }
                if ($course->adder_role !== 0) {
                    $course->course_status = 4;
                    $course->update();
                }
                $type = 4;
                $description = '查看需求';
                if ($course->adder_role == 0) {
                    $type = 11;
                    $description = '查看中介单';
                }
                // 保存日志
                $log_data = [
                    'user_id' => $user->id,
                    'amount' => '-'.$order->discount,
                    'type' => $type,
                    'description' => $description,
                    'created_at' => Carbon::now()
                ];
                $log_data_wechat = [
                    'user_id' => $user->id,
                    'amount' => '-'.($order->amount - $order->discount),
                    'type' => $type,
                    'description' => $description,
                    'created_at' => Carbon::now()
                ];
                if ($order->discount > 0) {
                    DB::table('bills')->insert($log_data);
                }
                DB::table('bills')->insert($log_data_wechat);
                // 当前时间
                /*$current = Carbon::now()->format('Y-m-d');
                // 查看是否有成交活动
                $deal_activity = Activity::where(['status' => 1,'type' => 3])->where('start_time', '<=', $current)
                    ->where('end_time', '>=', $current)->first();
                if ($deal_activity) {
                    deal_activity_log($user->id,$order->course_id,$deal_activity);
                }*/
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
        // 查询当前位置的所有推荐教师
        $teachers = User::with(['teacher_experience','teacher_info','teacher_education'])->where(['is_recommend' => 1,'role' => 3,'status' => 1])->paginate($page_size);
        foreach ($teachers as $teacher) {
            $teaching_year = 0;
            $subject = [];
            foreach ($teacher->teacher_experience as $experience) {
                $start_time = Carbon::parse($experience->start_time);
                $end_time = Carbon::parse($experience->end_time);
                $teaching_years = $start_time->diffInYears($end_time);
                $teaching_year += $teaching_years;
                // 课程
                $subject[] = explode(',',$experience->subject);
            }
            $teacher->teaching_year = $teaching_year;
            $teacher->subject = array_values(array_unique(array_reduce($subject,'array_merge',[])));
        }
        return $this->success('推荐教师列表',$teachers);
    }

    /**
     * 活动列表
     * @return \Illuminate\Http\JsonResponse
     */
    public function activity_list()
    {
        $data = \request()->all();
        $page_size = $data['page_size'] ?? 10;
        $status = $data['status'] ?? 1;
        // 用户角色
        $role = 4;
        $role_arr = ['','学生','家长','教师','机构'];
        $role_str = $role_arr[$role];
        $result = DB::table('activities')->whereRaw("FIND_IN_SET('$role_str',object)")->where('status',$status)->paginate($page_size);
        return $this->success('活动列表',$result);
    }

    /**
     * 活动详情
     * @return \Illuminate\Http\JsonResponse
     */
    public function activity_detail()
    {
        $data = \request()->all();
        $id = $data['id'] ?? 0;
        $result = Activity::find($id);
        if (!$result) {
            return $this->error('活动不存在');
        }
        $arr = ['','student_','parent_','teacher_','organ_'];
        $prefix = $arr[4];
        $result->first_reward = $result->{$prefix.'first_reward'};
        $result->second_reward = $result->{$prefix.'second_reward'};
        return $this->success('活动详情',$result);
    }

    /**
     * 获取banner图
     * @return \Illuminate\Http\JsonResponse
     */
    public function get_banner()
    {
        $data = \request()->all();
        $role = $data['role'] ?? 4;
        $role_arr = ['','学生','家长','教师','机构'];
        $role_str = $role_arr[$role];

        $result = Banner::whereRaw("FIND_IN_SET('$role_str',object)")->get();
        return $this->success('获取banner图',$result);
    }

    /**
     * 获取定位
     * @return \Illuminate\Http\JsonResponse
     */
    public function get_location()
    {
        $data_lat_lnt = request()->all();
        $longitude = $data_lat_lnt['longitude'] ?? '116.41339'; // 经度
        $latitude = $data_lat_lnt['latitude'] ?? '39.91092'; // 纬度
        $key = '4a81139b372ea849981ff499f53c6344'; // 替换为您自己的API密钥
        $url = "https://restapi.amap.com/v3/geocode/regeo?key={$key}&location={$longitude},{$latitude}";
        $response = file_get_contents($url);
        $data = json_decode($response, true);
        if ($data['status'] == 1 ) {
            $city = $data['regeocode']['addressComponent'];
            return $this->success('成功',$city);
        } else {
            return $this->error('失败，请重新加载');
        }
    }
}
