<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function update_info()
    {
        $data = \request()->all();
        $role = $data['role'] ?? 1;
        $rules = [
            'avatar' => 'required|url',
            'nickname' => 'required|regex:/^[\p{Han}a-zA-Z]+$/u|max:10',
            'name' => 'required|regex:/^[\p{Han}a-zA-Z]+$/u|max:10',
            'gender' => 'required',
            'province_id' => 'required',
            'city_id' => 'required',
            'district_id' => 'required',
        ];
        $messages = [
            'avatar.required' => '头像不能为空',
            'avatar.url' => '头像格式错误',
            'nickname.required' => '昵称不能为空',
            'nickname.regex' => '昵称只能为汉字或英文',
            'nickname.max' => '昵称最多为20个字符',
            'name.required' => '名称不能为空',
            'name.regex' => '名称只能为汉字或英文',
            'name.max' => '名称最多为20个字符',
            'gender.required' => '性别不能为空',
            'province_id.required' => '省份不能为空',
            'city_id.required' => '城市不能为空',
            'district_id.required' => '区县不能为空',
        ];
        if ($role) {
            $rules['grade'] = 'required';
            $messages['grade.required'] = '年级不能为空';
        }
        $validator = Validator::make($data,$rules,$messages);
        if ($validator->fails()) {
            $error = $validator->errors();
            return $this->error(json_encode(',',$error->all()));
        }
        $user_id = Auth::id();
        // 用户编号
        $data['number'] = create_number($data['city_id'],$user_id);
        $result = DB::table('users')->where('id',$user_id)->update($data);
        if (!$result) {
            return $this->error('更新失败');
        }
        return $this->success('更新成功');
    }

    /**
     * 绑定手机号
     * @return \Illuminate\Http\JsonResponse
     */
    public function bind_mobile()
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
        $user = Auth::user();
        $mobile = $data['mobile'];
        $user->mobile = $mobile;
        $user->save();
        return $this->success('绑定成功');
    }
}
