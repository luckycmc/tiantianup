<?php

namespace App\Admin\Forms;

use App\Models\Message;
use App\Models\SystemMessage;
use App\Models\User;
use Dcat\Admin\Admin;
use Dcat\Admin\Contracts\LazyRenderable;
use Dcat\Admin\Traits\LazyWidget;
use Dcat\Admin\Widgets\Form;
use Overtrue\EasySms\EasySms;
use Overtrue\EasySms\Exceptions\Exception;
use Overtrue\EasySms\Exceptions\NoGatewayAvailableException;
use Overtrue\EasySms\PhoneNumber;

class RefuseOrgan extends Form implements LazyRenderable
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
        $organ_info = \App\Models\Organization::find($id);
        $organ_info->status = 2;
        $organ_info->reason = $reason;
        $organ_info->reviewer_id = Admin::user()->id;
        $organ_info->update();
        // 发送通知
        if (SystemMessage::where('action',1)->value('site_message') == 1) {
            (new Message())->saveMessage($organ_info->user_id,0,'机构入驻','机构入驻审核','',0,3);
        }
        if (SystemMessage::where('action',1)->value('text_message') == 1) {
            $text = '机构入驻';
            // 发送短信
            $easySms = new EasySms($config);
            $user = User::find($organ_info->user_id);
            try {
                $number = new PhoneNumber($user->mobile);
                $easySms->send($number,[
                    'content'  => "【添添学】很抱歉，您的".$text."未通过审核",
                ]);
            } catch (Exception|NoGatewayAvailableException $exception) {
                return $this->response()
                    ->error('操作失败')
                    ->refresh();
            }
        }
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
