<?php

use App\Models\Activity;
use App\Models\Course;
use App\Models\Region;
use App\Models\ServicePrice;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * 用户编号
 * @param $city_id
 * @param $user_id
 * @return string
 */
function create_user_number($city_id,$user_id)
{
    // 查询省市code
    $region_code = Region::where('id',$city_id)->value('code');
    // 截取前四位
    $code = substr($region_code,0,4);
    // id补齐6位
    $id = pad($user_id,6);
    // 拼接编号
    $number = $code.date('Ymd').$id;
    return $number;
}

function create_course_number($course_id)
{
    $course_info = Course::with(['teaching_method','teaching_type','teaching_subject'])->find($course_id);
    $method_id = $course_info->teaching_method->id;
    $type_id = $course_info->teaching_type->id;
    $subject_id = $course_info->teaching_subject->id;
    $time = get_time();
    $number = $time.pad($method_id,2).pad($type_id,2).pad($subject_id,2).pad($course_id,3);
    return $number;
}

/**
 * 增项-创建课程编号
 * @param $course_id
 * @param $method
 * @param $adder_role
 * @return string
 */
function new_create_course_number($course_id,$method,$adder_role,$role) {
    $method_arr = [
        '线下' => '01',
        '线上' => '02',
        '线下/线上' => '03'
    ];
    $method_id = $method_arr[$method];
    $role_arr = [
        '4' => '01',
        '2' => '02',
        '0' => '03'
    ];
    $role_id = $role_arr[$adder_role];
    $time = get_time();
    if ($role == 1) {
        $number = $time.$method_id.pad($course_id,4);
    } else {
        $number = $time.$role_id.$method_id.pad($course_id,4);
    }
    return $number;
}

function create_df_number($course_id)
{
    $city_id = Course::where('id',$course_id)->value('city');
    // 查询省市code
    $region_code = Region::where('id',$city_id)->value('code');
    // 截取前四位
    $code = substr($region_code,0,4);
    $today = Carbon::now();
    $year = $today->format('y'); // 两位年份
    $month = $today->format('m'); // 两位月份
    $day = $today->format('d');   // 两位日期
    $number = 'DF'.$code.$year.$month.$day.pad($course_id,6);
    return $number;

}

function pad($id,$int)
{
    return str_pad($id,$int,'0',STR_PAD_LEFT);
}

function get_time()
{
    $date = Carbon::now();
    $year = $date->format('Y');
    $month = $date->format('m');
    $day = $date->format('d');

    $pattern = '/(\d{2}$)/'; // 匹配后两位数字的正则表达式

    preg_match($pattern, $year, $matches);
    $last_two_year_digits = $matches[0];

    preg_match($pattern, $month, $matches);
    $last_two_month_digits = $matches[0];

    preg_match($pattern, $day, $matches);
    $last_two_day_digits = $matches[0];

    $result = $last_two_year_digits . $last_two_month_digits . $last_two_day_digits;
    return $result;
}

function calculate_distance($lat1, $lon1, $lat2, $lon2) {
    $radius = 6371; // 地球半径，单位为公里

    $lat1Rad = deg2rad($lat1);
    $lon1Rad = deg2rad($lon1);
    $lat2Rad = deg2rad($lat2);
    $lon2Rad = deg2rad($lon2);

    $deltaLat = $lat2Rad - $lat1Rad;
    $deltaLon = $lon2Rad - $lon1Rad;

    $a = sin($deltaLat / 2) * sin($deltaLat / 2) + cos($lat1Rad) * cos($lat2Rad) * sin($deltaLon / 2) * sin($deltaLon / 2);
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

    $distance = round($radius * $c,2);

    return $distance;
}

function create_qr_code ($user_id) {
    $app_id = env('WECHAT_MINI_PROGRAM_APPID');
    $secret = env('WECHAT_MINI_PROGRAM_SECRET');
    $url = 'https://api.weixin.qq.com/cgi-bin/token';
    $token = Http::get($url,[
        'grant_type' => 'client_credential',
        'appid' => $app_id,
        'secret' => $secret
    ])->object()->access_token;
    $path = 'pages/login/index';
    $request_data = [
        'page'  => $path,
        "check_path" => true,
        'env_version'=>'develop',  //release 正式版
        'scene'=> $user_id,
    ];
    // $request_data = json_encode($request_data,320);
    $url = 'https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token='.$token;
    $result = Http::post($url, $request_data)->body();
    // dd($result->body());
    $file = "qr_code/" . $user_id . ".jpg";
    file_put_contents($file, $result);

    return '/'.$file;
}

function get_location ($longitude, $latitude) {
    $key = '4a81139b372ea849981ff499f53c6344'; // 替换为您自己的API密钥
    $url = "https://restapi.amap.com/v3/geocode/regeo?key={$key}&location={$longitude},{$latitude}";
    $response = file_get_contents($url);
    $data = json_decode($response, true);
    if ($data['status'] == 1 ) {
         return $data['regeocode']['addressComponent'];
    } else {
        return false;
    }
}

function get_long_lat($province,$city,$district,$address) {
    $key = '4a81139b372ea849981ff499f53c6344'; // 替换为您自己的API密钥
    $url = "https://restapi.amap.com/v3/geocode/geo?key={$key}&address={$province}.{$city}.{$district}.$address";
    $response = file_get_contents($url);
    $data = json_decode($response, true);
    $info = $data['geocodes'][0]['location'];
    $arr = explode(',',$info);
    return $arr;
}

function get_access_token () {
    $param = [
        'appid' => env('WECHAT_MINI_PROGRAM_APPID'),
        'secret' => env('WECHAT_MINI_PROGRAM_SECRET'),
        'grant_type' => 'client_credential'
    ];
    $url = 'https://api.weixin.qq.com/cgi-bin/token';
    $result = Http::get($url,$param);
    $info = json_decode($result->body(),true);
    return $info['access_token'];
}

// 获取活动奖励
function get_reward($type,$role) {
    $word_arr = ['','学生','家长','教师','机构'];
    $role_word = $word_arr[$role];
    switch ($type) {
        case 1:
            // 邀新活动
            $arr = ['','student_','parent_','teacher_','organ_'];
            $prefix = $arr[$role];
            $first_field = $prefix.'first_reward';
            $second_field = $prefix.'second_reward';
            $new_field = $prefix.'new_reward';
            $type_field = $prefix.'reward_type';
            // 查询奖励
            $reward = Activity::where(['type' => 1,'status' => 1])->whereRaw("FIND_IN_SET('$role_word',object)")->select('id',$first_field,$second_field,$new_field,$type_field)->first();
            break;
        case 2:
            // 教师注册
            $reward = Activity::where(['type' => 2,'status' => 1])->select('id','teacher_real_auth_reward','teacher_cert_reward','teacher_career_reward','teacher_image_reward')->first();
            break;
        case 3:
        default:
            // 成交活动
            $deal_arr = ['','','parent_','teacher_','organ_'];
            $deal_prefix = $deal_arr[$role];
            $deal_reward = $deal_prefix.'deal_reward';
            $deal_type_field = $deal_prefix.'deal_reward_type';
            // 查询奖励
            $reward = Activity::where(['type' => 3,'status' => 1])->whereRaw("FIND_IN_SET('$role_word',object)")->select('id',$deal_reward,$deal_type_field)->first();
            break;
    }
    return $reward;
}

// 获取服务费
function get_service_price($type,$province_id,$city_id,$district_id) {
    $today = Carbon::now()->toDateString();
    $info = ServicePrice::where(['type' => $type,['start_time','<=',$today],['end_time','>=',$today]])->orderByDesc('created_at')->first();
    // 判断当前城市是否为执行地区
    $address_ids = $info->areas()->pluck('area_id')->toArray();
    if (!in_array($province_id,$address_ids) && !in_array($city_id,$address_ids) && !in_array($district_id,$address_ids)) {
        return 0;
    }
    return $info->price;
}

function get_official_access_token() {
    $appid = env('WECHAT_OFFICIAL_APPID');
    $secret = env('WECHAT_OFFICIAL_SECRET');
    $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$appid.'&secret='.$secret;
    $result = Http::get($url);
    $arr = json_decode($result->body(),true);
    return $arr['access_token'];
}

function send_official_message($openid,$data) {
    $access_token = get_official_access_token();
    $url = 'https://api.weixin.qq.com/cgi-bin/message/subscribe/bizsend?access_token='.$access_token;
    $post_data = [
        'touser' => $openid,
        'template_id' => 'QmZZLk04SbpweF_vIr71BMnR1_B6xeJMP-dehoBZTx0',
        'appid' => env('WECHAT_MINI_PROGRAM_APPID'),
        'data' => $data
    ];
    $result = Http::post($url,$post_data);
    dd($result->body());
}

function get_official_openid($union_id) {
    $access_token = get_official_access_token();
    $url = 'https://api.weixin.qq.com/cgi-bin/user/get?access_token='.$access_token.'&next_openid=';
    $result = Http::get($url)->body();
    $arr = json_decode($result,true);
    $openid_arr = $arr['data']['openid'];
    $open_id = '';
    foreach ($openid_arr as $v) {
        $union_url = 'https://api.weixin.qq.com/cgi-bin/user/info?access_token='.$access_token.'&openid='.$v.'&lang=zh_CN';
        $union_result = Http::get($union_url)->body();
        $array = json_decode($union_result,true);
        $user_union_id = $array['union_id'];
        // 查询数据库
        if ($union_id == $user_union_id) {
            $user = User::where('union_id', $user_union_id)->first();
            $user->official_open_id = $v;
            $user->update();
            $open_id = $v;
        }
    }
    return $open_id;
}

// 邀新活动
function invite_activity_log ($parent_id,$user_id,$role,$invite_activity) {
    // 查询父级信息
    $parent = User::find($parent_id);
    // 获取活动奖励
    $reward = get_reward(1,$parent->role);
    $arr = ['','student_','parent_','teacher_','organ_'];
    $prefix = $arr[$parent->role];
    $first_field = $prefix.'first_reward';
    $second_field = $prefix.'second_reward';
    $new_field = $prefix.'new_reward';

    // 发放奖励
    $parent->withdraw_balance += $reward->$first_field;
    $parent->total_income += $reward->$first_field;
    $parent->update();
    $user = User::find($user_id);
    $user->withdraw_balance += $reward->$new_field;
    $user->total_income += $reward->$new_field;
    $user->update();
    // 保存日志
    $parent_bill_data = [
        'user_id' => $parent->id,
        'amount' => $reward->$first_field,
        'type' => 2,
        'description' => '邀新奖励',
        'created_at' => Carbon::now()
    ];
    $user_bill_data = [
        'user_id' => $user->id,
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
        'activity_id' => $invite_activity->id,
        'created_at' => Carbon::now()
    ];
    $user_activity_log = [
        'user_id' => $user->id,
        'username' => $user->name,
        'role' => $role,
        'first_child' => 0,
        'second_child' => 0,
        'activity_id' => $invite_activity->id,
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
        $granpa->total_income += $reward->$second_field;
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
            'activity_id' => $invite_activity->id,
            'type' => $invite_activity->type,
            'created_at' => Carbon::now()
        ];
        DB::table('bills')->insert($granpa_bill_data);
        DB::table('activity_log')->insert($granpa_activity_log);
    }
}

// 教师注册活动
function teacher_activity_log ($teacher_id,$field,$project,$description,$teacher_activity) {
    // 查询奖励
    $reward = get_reward(2,3);
    $amount = $reward->$field;
    $user = User::find($teacher_id);
    $user->withdraw_balance += $amount;
    $user->total_income += $amount;
    $user->update();
    if ($amount > 0) {
        $bill_log = [
            'user_id' => $user->id,
            'amount' => $amount,
            'type' => 6,
            'description' => $description,
            'created_at' => Carbon::now()
        ];
        $activity_log = [
            'user_id' => $user->id,
            'username' => $user->name,
            'activity_id' => $teacher_activity->id,
            'amount' => $amount,
            'project' => $project,
            'type' => $teacher_activity->type,
            'description' => $description,
            'created_at' => Carbon::now()
        ];
        DB::table('bills')->insert($bill_log);
        DB::table('activity_log')->insert($activity_log);
    }
}

// 成交活动
function deal_activity_log($user_id,$course_id,$deal_activity) {
    // 教师
    $teacher = User::find($user_id);
    // 需求
    $course = Course::find($course_id);
    $role = $course->adder_role;
    Log::info('role: '.$role);
    // 查询奖励
    $reward = get_reward(3,$role);
    $adder_id = $course->adder_id;
    $adder = User::find($adder_id);
    // 成交活动
    $deal_arr = ['','','parent_','teacher_','organ_'];
    $deal_prefix = $deal_arr[$role];
    $deal_reward = $deal_prefix.'deal_reward';
    // 家长或机构
    $activity_log = [
        'user_id' => $adder_id,
        'username' => $adder->name,
        'number' => $adder->number,
        'role' => $role,
        'amount' => $reward->$deal_reward,
        'type' => $deal_activity->type,
        'created_at' => Carbon::now()
    ];
    $bill_log = [
        'user_id' => $adder_id,
        'amount' => $reward->$deal_reward,
        'type' => 9,
        'description' => '成交奖励',
        'created_at' => Carbon::now()
    ];
    $user = User::find($adder_id);
    $user->withdraw_balance += $reward->$deal_reward;
    $user->total_income += $reward->$deal_reward;
    $user->update();
    // 教师
    $teacher_reward = get_reward(3,3);
    $teacher_activity_log = [
        'user_id' => $adder_id,
        'username' => $teacher->name,
        'number' => $teacher->number,
        'role' => 3,
        'amount' => $teacher_reward->teacher_deal_reward,
        'type' => $deal_activity->type,
        'created_at' => Carbon::now()
    ];
    $teacher_bill_log = [
        'user_id' => $user_id,
        'amount' => $teacher_reward->teacher_deal_reward,
        'type' => 9,
        'description' => '成交奖励',
        'created_at' => Carbon::now()
    ];
    $teacher->withdraw_balance += $teacher_reward->teacher_deal_reward;
    $teacher->total_income += $teacher_reward->teacher_deal_reward;
    $teacher->update();

    DB::table('activity_log')->insert($teacher_activity_log);
    DB::table('activity_log')->insert($activity_log);
    DB::table('bills')->insert($bill_log);
    DB::table('bills')->insert($teacher_bill_log);
}

/**
 * http请求
 * @param string $url  请求的地址
 * @param string $data 请求参数
 */
function https_request($url, $data = null)
{
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    if (!empty($data)) {
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    }
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $output = curl_exec($curl);
    curl_close($curl);
    return $output;
}

function handel_subject($subject)
{

    // 将字符串转换为数组，并去除重复的字段
    $array = explode(",", $subject);
    $uniqueArray = array_unique($array);
    return $uniqueArray;
}
