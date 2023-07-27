<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class IndexController extends Controller
{
    public function upload()
    {
        $data = request()->all();
        $file = request()->file('file');
        $pathname = $data['pathname'] ?? 'user';

        $disk = Storage::disk('cosv5');
        $upload_path = 'upload/imgs/'.$pathname.'/' . date("Ym/d", time());
        //将图片上传到OSS中，并返回图片路径信息 值如:imgs/1234.jpeg
        $path = $disk->put($upload_path, $file);
        $url = $disk->url($path);
        $url = explode('?',$url)[0];
        return $this->success('上传成功',compact('url'));
    }
}
