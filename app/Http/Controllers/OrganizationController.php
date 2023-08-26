<?php

namespace App\Http\Controllers;

use App\Models\BaseInformation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class OrganizationController extends Controller
{
    /**
     * 填写信息
     * @return \Illuminate\Http\JsonResponse
     */
    public function create()
    {
        $data = \request()->except('images');
        $rules = [];
        $messages = [];
        $validator = Validator::make($data,$rules,$messages);
        if ($validator->fails()) {
            $error = $validator->errors();
            return $this->error(implode(',',$error->all()));
        }
        // 当前用户
        $user = Auth::user();
        // 存入机构
        $data['created_at'] = Carbon::now();
        $data['user_id'] = $user->id;
        $id = DB::table('organizations')->insertGetId($data);
        $images = \request()->input('images');
        if ($images) {
            $image_data = [];
            foreach ($images as $v) {
                $image_data[] = [
                    'organ_id' => $id,
                    'url' => $v,
                    'created_at' => Carbon::now()
                ];
            }
            // 保存图片
            DB::table('organ_images')->insert($image_data);
        }
        return $this->success('提交成功');
    }

    /**
     * 生成订单
     * @return \Illuminate\Http\JsonResponse
     */
    public function create_teacher_order()
    {
        $data = \request()->all();
        $teacher_id = $data['teacher_id'] ?? 0;
        // 查询教师
        $teacher = User::where(['role' => 3,'id' => $teacher_id])->first();
        if (!$teacher) {
            return $this->error('教师不存在');
        }
        // 当前用户
        $user = Auth::user();
        $out_trade_no = app('snowflake')->id();
        // 查询服务费
        $service_price = BaseInformation::value('service_price');
        $order_data = [
            'user_id' => $user->id,
            'role' => 4,
            'teacher_id' => $teacher_id,
            'out_trade_no' => $out_trade_no,
            'amount' => $service_price,
            'discount' => 0,
            'status' => 0,
            'created_at' => Carbon::now()
        ];
        // 保存数据
        $result = DB::table('user_teacher_orders')->insert($order_data);
        if (!$result) {
            return $this->error('生成订单失败');
        }
        return $this->success('生成订单成功',compact('out_trade_no'));
    }
}
