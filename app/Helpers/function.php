<?php

use App\Models\Course;
use App\Models\Region;
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
    // dd($course_info);
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
        // 'page'  => $path,
        "check_path" => true,
        'env_version'=>'develop',  //release 正式版
        'scene'=> $user_id,
    ];
    // $request_data = json_encode($request_data,320);
    $url = 'https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token='.$token;
    $result = Http::post($url, $request_data);
    // dd($result->body());

    $upload_path = "qrcode/".$user_id.",jpg";
    $disk = Storage::disk('cosv5');
    $path = $disk->put($upload_path, $result);
    $url = $disk->url($path);
    $url = explode('?',$url)[0];
    // return $url;
}
