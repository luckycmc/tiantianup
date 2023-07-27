<?php

namespace App\Http\Controllers;

use App\Models\User;
use EasyWeChat\Factory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
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
        $app = Factory::miniProgram($config);
        $session =$app->auth->session($code);
        if (!isset($session['session_key'])) {
            return $this->error('登陆失败');
        }
        // 判断用户是否存在
        $is_user = User::where(['open_id' => $session['openid']])->first();
        if (!$is_user) {
            $new_user = new User();
            $new_user->open_id = $session['openid'];
            $new_user->save();
            $is_user = $new_user;
        }

        // 用户登录
        $token = JWTAuth::fromUser($is_user);
        $user_id = $is_user->id;
        //设置token
        Redis::set('TOKEN:'.$is_user->id,$token);
        return $this->success('登录成功',compact('token','user_id'));
    }

    public function mobile_login()
    {
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
            return $this->error(json_encode(',',$error->all()));
        }
        // 校验验证码
        $sendcode = Redis::get($data['mobile']);
        if(!$sendcode || $sendcode!=$data['code']) return $this->error('验证码不正确');
        // 查询用户是否存在
        $is_user = User::where('mobile',$data['mobile'])->first();
        if (!$is_user) {
            // 注册新用户
            $new_user = new User();
            $new_user->mobile = $data['mobile'];
            $new_user->save();
            $is_user = $new_user;
        }
        // 用户登录
        $token = JWTAuth::fromUser($is_user);
        //设置token
        Redis::set('TOKEN:'.$is_user->id,$token);
        return $this->success('登录成功',compact('token'));
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
            $number = new PhoneNumber($data['mobile']);
            $easySms->send($number,[
                'content'  => '【添添向尚】您的验证码'.$code.'。如非本人操作，请忽略本短信',
            ]);
        } catch (Exception|NoGatewayAvailableException $exception) {
            return $this->error($exception->getResults());
        }
        Redis::setex($mobile,300,$code);
        return $this->success('发送成功');
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
}
