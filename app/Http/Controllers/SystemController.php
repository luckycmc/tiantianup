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
}
