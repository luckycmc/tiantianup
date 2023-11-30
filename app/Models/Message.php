<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Message extends Model
{
	use HasDateTimeFormatter;

    public function saveMessage($user_id,$from_user_id,$name,$content,$course_id,$platform,$type)
    {
        $model = new self();
        $model->user_id = $user_id;
        $model->from_user_id = $from_user_id;
        $model->name = $name;
        $model->content = $content;
        $model->course_id = $course_id ?? null;
        $model->send_platform = $platform;
        $model->status = 0;
        $model->type = $type;
        // $where = ['user_id' => $user_id,'from_user_id' => $from_user_id,'type' => $type,'course_id' => $course_id,'status' => 0];
        $model->save();
        /*if (!DB::table('messages')->where($where)->exists()) {
            $model->save();
        }*/
    }
}
