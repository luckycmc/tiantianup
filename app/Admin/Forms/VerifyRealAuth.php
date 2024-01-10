<?php

namespace App\Admin\Forms;

use App\Models\Activity;
use App\Models\Message;
use App\Models\SystemMessage;
use App\Models\TeacherInfo;
use App\Models\TeacherTag;
use App\Models\User;
use Carbon\Carbon;
use Dcat\Admin\Contracts\LazyRenderable;
use Dcat\Admin\Traits\LazyWidget;
use Dcat\Admin\Widgets\Form;
use Illuminate\Support\Facades\DB;
use Overtrue\EasySms\EasySms;
use Overtrue\EasySms\Exceptions\Exception;
use Overtrue\EasySms\Exceptions\NoGatewayAvailableException;
use Overtrue\EasySms\PhoneNumber;

class VerifyRealAuth extends Form implements LazyRenderable
{
    use LazyWidget;
    /**
     * Handle the form request.
     *
     * @param array $input
     *
     * @return mixed
     */
    public function handle(array $input)
    {
        $config = config('services.sms');
        $id = $this->payload['id'] ?? null;
        $id_card_no = $input['id_card_no'] ?? '';
        $real_name = $input['real_name'] ?? '';
        $teacher_info = TeacherInfo::find($id);
        $teacher_info->status = 1;
        $teacher_info->reason = null;
        $teacher_info->id_card_no = $id_card_no;
        $teacher_info->real_name = $real_name;
        $user = User::find($teacher_info->user_id);
        $user->is_real_auth = 1;
        $user->update();

        // 发送通知
        if (SystemMessage::where('action',4)->value('site_message') == 1) {
            (new Message())->saveMessage($teacher_info->user_id,0,'实名认证','实名认证审核通过',0,0,3);
        }
        if (SystemMessage::where('action',4)->value('text_message') == 1) {
            $text = '实名认证';
            // 发送短信
            $easySms = new EasySms($config);

            try {
                $number = new PhoneNumber($user->mobile);
                $easySms->send($number,[
                    'content'  => "【添添学】恭喜您，您的".$text."已通过审核",
                ]);
            } catch (Exception|NoGatewayAvailableException $exception) {
                return $this->response()
                    ->error('操作失败')
                    ->refresh();
            }
        }
        $tag = '实名认证';
        $tag_info = [
            'user_id' => $user->id,
            'tag' => $tag,
            'type' => 3
        ];
        // 保存日志
        DB::transaction(function () use ($teacher_info,$user,$tag_info) {
            $teacher_info->update();
            $user->update();
            TeacherTag::updateOrCreate(['user_id' => $user->id,'tag' => $tag_info['tag']],$tag_info);
        });

        // 当前时间
        $current = Carbon::now()->format('Y-m-d');
        // 查看是否有注册活动
        $teacher_activity = Activity::where(['status' => 1,'type' => 2])->where('start_time', '<=', $current)
            ->where('end_time', '>=', $current)->first();
        // 查询是否已获得奖励
        $is_reward = \App\Models\ActivityLog::where(['user_id' => $user->id,'activity_id' => $teacher_activity->id,'description' => '实名认证审核通过'])->exists();
        if ($teacher_activity && !$is_reward) {
            teacher_activity_log($teacher_info->user_id,'teacher_real_auth_reward','实名认证','实名认证审核通过',$teacher_activity);
        }

        return $this
            ->response()
            ->success('操作成功')
            ->refresh();
    }

    /**
     * Build a form here.
     */
    public function form()
    {
        $this->text('id_card_no','身份证号')->required();
        $this->text('real_name','真实姓名')->required();
    }

    /**
     * The data of the form.
     *
     * @return array
     */
    public function default()
    {
        return [
            'id_card_no'  => '',
            'real_name' => '',
        ];
    }
}
