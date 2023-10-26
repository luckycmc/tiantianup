<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\User;
use Carbon\Carbon;
use EasyWeChat\Factory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Overtrue\EasySms\EasySms;
use Overtrue\EasySms\Exceptions\Exception;
use Overtrue\EasySms\Exceptions\NoGatewayAvailableException;
use Overtrue\EasySms\PhoneNumber;
use Tymon\JWTAuth\Facades\JWTAuth;

class LoginController extends Controller
{
    /**
     * 微信登录
     * @return \Illuminate\Http\JsonResponse
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     */
    public function wx_login()
    {
        $config = config('wechat.mini_program.default');
        $data = \request()->all();
        $code = $data['code'] ?? '';
        $union_id = $data['union_id'] ?? 0;
        // $iv = $data['iv'];
        // $encryptData = $data['encryptedData'];
        $app = Factory::miniProgram($config);
        $session = $app->auth->session($code);

        Log::info('session: ',$session);
        if (!isset($session['session_key'])) {
            return $this->error('登陆失败');
        }
        // $decryptedData = $app->encryptor->decryptData($session['session_key'], $iv, $encryptData);
        // Log::info('decryptedData: '.$decryptedData);
        // 判断用户是否存在
        $is_new = 0;
        $is_user = User::where(['open_id' => $session['openid']])->first();
        if (!$is_user) {
            $is_new = 1;
            $new_user = new User();
            $new_user->open_id = $session['openid'];
            $new_user->union_id = $union_id;
            $new_user->parent_id = $data['parent_id'] ?? null;
            $new_user->save();
            $is_user = $new_user;
        }

        // 用户登录
        $token = JWTAuth::fromUser($is_user);
        $user_id = $is_user->id;
        //设置token
        Redis::set('TOKEN:'.$is_user->id,$token);
        $is_role = $is_user->role ?? 0;
        // 当前时间
        $current = Carbon::now()->format('Y-m-d');
        // 查看是否有注册活动
        $invite_activity = Activity::where(['status' => 1])->where('start_date', '<=', $current)
        ->where('end_date', '>=', $current)->first();
        if ($invite_activity && $is_new && isset($data['parent_id'])) {
            // 获取活动奖励
            $reward = get_reward(1,$is_role);
            $arr = ['','student_','parent_','teacher_','organ_'];
            $prefix = $arr[$is_role];
            $first_field = $prefix.'first_reward';
            $second_field = $prefix.'second_reward';
            $new_field = $prefix.'new_reward';
            // 查询父级信息
            $parent = User::find($data['parent_id']);
            // 发放奖励
            $parent->withdraw_balance += $reward->$first_field;
            $parent->total_balance += $reward->$first_field;
            $parent->update();
            $is_user->withdraw_balance += $reward->$new_field;
            $is_user->total_balance += $reward->$new_field;
            $is_user->update();
            // 保存日志
            $parent_bill_data = [
                'user_id' => $parent->id,
                'amount' => $reward->$first_field,
                'type' => 2,
                'description' => '邀新奖励',
                'created_at' => Carbon::now()
            ];
            $user_bill_data = [
                'user_id' => $is_user->id,
                'amount' => $reward->$new_field,
                'type' => 2,
                'description' => '邀新奖励',
                'created_at' => Carbon::now()
            ];
            // 保存活动记录
            $parent_activity_log = [
                'user_id' => $parent->id,
                'username' => $parent->name,
                'role' => $parent->role,
                'first_child' => $parent->child->count(),
                'second_child' => $parent->grandson->count(),
                'created_at' => Carbon::now()
            ];
            $user_activity_log = [
                'user_id' => $is_user->id,
                'username' => $is_user->name,
                'role' => $is_role,
                'first_child' => 0,
                'second_child' => 0,
                'created_at' => Carbon::now()
            ];
            DB::table('bills')->insert($parent_bill_data);
            DB::table('bills')->insert($user_bill_data);
            DB::table('activity_log')->insert($parent_activity_log);
            DB::table('activity_log')->insert($user_activity_log);

            // 查询祖父级
            if (isset($parent->parent_id)) {
                $granpa = User::find($parent->parent_id);
                $granpa->withdraw_balance += $reward->$second_field;
                $granpa->total_balance += $reward->$second_field;
                $granpa->update();
                // 保存活动记录
                $granpa_bill_data = [
                    'user_id' => $granpa->id,
                    'amount' => $reward->$second_field,
                    'type' => 2,
                    'description' => '邀新奖励',
                    'created_at' => Carbon::now()
                ];
                $granpa_activity_log = [
                    'user_id' => $granpa->id,
                    'username' => $granpa->name,
                    'role' => $granpa->role,
                    'first_child' => $granpa->child->count(),
                    'second_child' => $granpa->grandson->count(),
                    'created_at' => Carbon::now()
                ];
                DB::table('bills')->insert($granpa_bill_data);
                DB::table('activity_log')->insert($granpa_activity_log);
            }
            //
        }
        return $this->success('登录成功',compact('token','user_id','is_role'));
    }

    /**
     * 手机号登录
     * @return \Illuminate\Http\JsonResponse
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     */
    public function mobile_login()
    {
        $config = config('wechat.mini_program.default');
        $data = \request()->all();
        $rules = [
            'mobile' => 'required|phone_number',
            'code' => 'required'
        ];
        $messages = [
            'mobile.required' => '手机号不能为空',
            'mobile.phone_number' => '手机号格式不正确',
            'code.required' => '验证码不能为空'
        ];
        $validator = Validator::make($data,$rules,$messages);
        if ($validator->fails()) {
            $error = $validator->errors();
            return $this->error(implode(',',$error->all()));
        }
        // 校验验证码
        $sendcode = Redis::get($data['mobile']);
        if(!$sendcode || $sendcode!=$data['code']) return $this->error('验证码不正确');
        // 查询用户是否存在
        $is_user = User::where('mobile',$data['mobile'])->first();
        // 获取open_id
        $open_id = '';
        if (isset($data['wx_code'])) {
            $app = Factory::miniProgram($config);
            $session = $app->auth->session($data['wx_code']);
            if (!isset($session['session_key'])) {
                return $this->error('登陆失败');
            }
            $open_id = $session['openid'];
        }
        if (!$is_user) {
            // 注册新用户
            $new_user = new User();
            $new_user->mobile = $data['mobile'];
            $new_user->parent_id = $data['parent_id'] ?? null;
            $new_user->open_id = $open_id;
            $new_user->save();
            $is_user = $new_user;
        }
        if (!$is_user->invite_qrcode) {
            $qrcode = create_qr_code($is_user->id);
            $is_user->invite_qrcode = env('APP_URL').$qrcode;
            $is_user->save();
        }
        // 用户登录
        $token = JWTAuth::fromUser($is_user);
        //设置token
        Redis::set('TOKEN:'.$is_user->id,$token);
        // dd($is_user->role);
        $is_role = $is_user->role ?? 0;
        return $this->success('登录成功',compact('token','is_role'));
    }

    /**
     * 发送验证码
     * @return \Illuminate\Http\JsonResponse
     */
    public function send_sms()
    {
        // 发送短信
        $config = config('services.sms');
        $data = request()->all();
        $mobile = $data['mobile'] ?? '';
        $rules = [
            'mobile' => 'required|phone_number'
        ];
        $message = [
            'mobile.required' => '手机号码不能为空',
            'mobile.phone_number' => '手机号码格式错误',
        ];
        $validator = Validator::make($data,$rules,$message);
        if ($validator->fails()) {
            $error = $validator->errors();
            return $this->error($error->all());
        }
        $code = mt_rand(1000,9999);
        $easySms = new EasySms($config);
        try {
            /*$number = new PhoneNumber($data['mobile']);
            $easySms->send($number,[
                'content'  => '【添添向尚】您的验证码'.$code.'。如非本人操作，请忽略本短信',
            ]);*/
        } catch (Exception|NoGatewayAvailableException $exception) {
            return $this->error($exception->getResults());
        }
        Redis::setex($mobile,300,$code);
        return $this->success('发送成功',$code);
    }

    /**
     * 校验验证码
     * @return \Illuminate\Http\JsonResponse
     */
    public function verify_code()
    {
        $data = \request()->all();
        $mobile = $data['mobile'] ?? 0;
        $code = $data['code'] ?? 0;
        $sendcode = Redis::get($mobile);
        if(!$sendcode || $sendcode!=$code) return $this->error('验证码不正确');
        return $this->success('验证通过');
    }

    public function get_open_id()
    {
        $data = \request()->all();
        $code = $data['code'] ?? '';
    }

    /**
     * 邀请机构成员
     * @return \Illuminate\Http\JsonResponse
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     */
    public function invite_member()
    {
        $config = config('wechat.mini_program.default');
        $data = \request()->all();
        $organ_role_id = $data['organ_role_id'] ?? 1;
        $parent_id = $data['parent_id'] ?? 0;
        // 查询上级id
        $user = User::find($parent_id);
        if (!$user) {
            return $this->error('邀请人不存在');
        }
        // 获取open_id
        $open_id = '';
        if (isset($data['wx_code'])) {
            $app = Factory::miniProgram($config);
            $session = $app->auth->session($data['wx_code']);
            if (!isset($session['session_key'])) {
                return $this->error('登陆失败');
            }
            $open_id = $session['openid'];
        }
        // 注册新用户
        $member = new User();
        $member->role = 4;
        $member->parent_id = $parent_id;
        $member->organ_role_id = $organ_role_id;
        $member->open_id = $open_id;
        $member->mobile = $data['mobile'] ?? null;
        $member->name = $data['name'] ?? null;
        $member->save();
        // 用户登录
        $token = JWTAuth::fromUser($member);
        //设置token
        Redis::set('TOKEN:'.$member->id,$token);
        $is_role = $member->role ?? 0;
        return $this->success('登录成功',compact('token','is_role'));
    }
}
