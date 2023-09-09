<?php

namespace App\Http\Controllers;

use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    /**
     * 消息列表
     * @return \Illuminate\Http\JsonResponse
     */
    public function list()
    {
        $data = \request()->all();
        $page_size = $data['page_size'] ?? 10;
        $status = $data['status'] ?? 0;
        // 当前用户
        $user = Auth::user();
        $result = Message::where(['user_id' => $user->id,'status' => $status])->paginate($page_size);
        return $this->success('消息列表',$result);
    }

    /**
     * 设为已读
     * @return \Illuminate\Http\JsonResponse
     */
    public function update_status()
    {
        $data = \request()->all();
        $message_id = $data['message_id'] ?? 0;
        $message_info = Message::find($message_id);
        if (!$message_info) {
            return $this->error('消息不存在');
        }
        $message_info->status = 1;
        $message_info->save();
        return $this->success('设为已读');
    }
}
