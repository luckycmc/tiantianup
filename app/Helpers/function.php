<?php

use App\Models\Region;

/**
 * 用户编号
 * @param $city_id
 * @param $user_id
 * @return string
 */
function create_number($city_id,$user_id)
{
    // 查询省市code
    $region_code = Region::where('id',$city_id)->value('code');
    // 截取前四位
    $code = substr($region_code,0,4);
    // id补齐6位
    $id = str_pad($user_id,6,'0',STR_PAD_LEFT);
    // 拼接编号
    $number = $code.date('Ymd').$id;
    return $number;
}