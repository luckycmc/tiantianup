<?php

use App\Models\Activity;
use App\Models\Course;
use App\Models\Region;
use App\Models\ServicePrice;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
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

function pad($id,$int)
{
    return str_pad($id,$int,'0',STR_PAD_LEFT);
}

function get_time()
{
    $date = Carbon::now();
    $year = $date->format('Y');
    $month = $date->format('m');

    $pattern = '/(\d{2}$)/'; // 匹配后两位数字的正则表达式

    preg_match($pattern, $year, $matches);
    $last_two_year_digits = $matches[0];

    preg_match($pattern, $month, $matches);
    $last_two_month_digits = $matches[0];

    $result = $last_two_year_digits . $last_two_month_digits;
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
         return $city = $data['regeocode']['addressComponent'];
    } else {
        return false;
    }
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
            $reward = Activity::where(['type' => 1,'status' => 1])->whereRaw("FIND_IN_SET('$role_word',object)")->select($first_field,$second_field,$new_field,$type_field)->first();
            break;
        case 2:
            // 教师注册
            $reward = Activity::where(['type' => 2,'status' => 1])->select('teacher_real_auth_reward','teacher_cert_reward','teacher_career_reward','teacher_image_reward')->first();
            break;
        case 3:
        default:
            // 成交活动
            $deal_arr = ['','','parent_','teacher_','organ_'];
            $deal_prefix = $deal_arr[$role];
            $deal_reward = $deal_prefix.'deal_reward';
            $deal_type_field = $deal_prefix.'deal_reward_type';
            // 查询奖励
            $reward = Activity::where(['type' => 2,'status' => 1])->whereRaw("FIND_IN_SET('$role_word',object)")->select($deal_reward,$deal_type_field)->first();
            break;
    }
    return $reward;
}

// 获取服务费
function get_service_price($type,$province,$city) {
    $today = Carbon::now()->toDateString();
    $info = ServicePrice::where(['type' => $type,['start_time','<=',$today],['end_time','>=',$today]])->whereRaw("FIND_IN_SET('$province',region)")->orWhereRaw("FIND_IN_SET('$city',region)")->orderByDesc('created_at')->first();
    if (!$info) {
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
