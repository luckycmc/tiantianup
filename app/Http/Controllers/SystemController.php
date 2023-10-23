<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Models\SystemImage;
use Illuminate\Http\Request;

class SystemController extends Controller
{
    /**
     * 获取小程序图片
     * @return \Illuminate\Http\JsonResponse
     */
    public function get_program_image()
    {
        $result = SystemImage::first();
        return $this->success('小程序图片',$result);
    }

    /**
     * 获取banner图
     * @return \Illuminate\Http\JsonResponse
     */
    public function get_banner()
    {
        $data = \request()->all();
        $role = $data['role'];
        $role_arr = ['','学生','家长','教师','机构'];
        $role_str = $role_arr[$role];

        $result = Banner::whereRaw("FIND_IN_SET('$role_str',object)")->get();
        return $this->success('获取banner图',$result);
    }
}
