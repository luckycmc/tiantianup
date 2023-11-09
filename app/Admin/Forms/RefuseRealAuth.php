<?php

namespace App\Admin\Forms;

use App\Models\Message;
use App\Models\SystemMessage;
use App\Models\TeacherInfo;
use App\Models\User;
use Dcat\Admin\Contracts\LazyRenderable;
use Dcat\Admin\Traits\LazyWidget;
use Dcat\Admin\Widgets\Form;
use Overtrue\EasySms\EasySms;
use Overtrue\EasySms\Exceptions\Exception;
use Overtrue\EasySms\Exceptions\NoGatewayAvailableException;
use Overtrue\EasySms\PhoneNumber;

class RefuseRealAuth extends Form implements LazyRenderable
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
        $reason = $input['reason'] ?? '';
        $teacher_info = TeacherInfo::find($id);
        $teacher_info->status = 3;
        $teacher_info->reason = $reason;
        $teacher_info->update();
        $user = User::find($teacher_info->user_id);
        // 发送通知
        if (SystemMessage::where('action',4)->value('site_message') == 1) {
            (new Message())->saveMessage($teacher_info->user_id,0,'实名认证','实名认证审核失败','',0,3);
        }
        if (SystemMessage::where('action',4)->value('text_message') == 1) {
            $text = '实名认证';
            // 发送短信
            $easySms = new EasySms($config);
            try {
                $number = new PhoneNumber($user->mobile);
                $easySms->send($number,[
                    'content'  => "【添添向尚】很抱歉，您的".$text."未通过审核",
                ]);
            } catch (Exception|NoGatewayAvailableException $exception) {
                return $this->response()
                    ->error('操作失败')
                    ->refresh();
            }
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
        $this->text('reason','拒绝原因')->required();
    }

    /**
     * The data of the form.
     *
     * @return array
     */
    public function default()
    {
        return [
            'reason'  => '',
        ];
    }
}
