<?php

namespace App\Admin\Forms;

use App\Models\Message;
use App\Models\SystemMessage;
use App\Models\User;
use App\Models\Withdraw;
use Dcat\Admin\Contracts\LazyRenderable;
use Dcat\Admin\Traits\LazyWidget;
use Dcat\Admin\Widgets\Form;
use Overtrue\EasySms\EasySms;
use Overtrue\EasySms\Exceptions\Exception;
use Overtrue\EasySms\Exceptions\NoGatewayAvailableException;
use Overtrue\EasySms\PhoneNumber;

class RefuseWithdraw extends Form implements LazyRenderable
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
        $info = Withdraw::find($id);
        $info->status = 1;
        $info->reason = $reason;
        $info->update();
        // 用户
        $user = User::find($info->user_id);
        $user->withdraw_balance += $info->amount;
        $user->update();
        $user = User::find($info->user_id);
        // 发送通知
        if (SystemMessage::where('action',12)->value('site_message') == 1) {
            (new Message())->saveMessage($user->id,0,'提现审核审核','提现申请未通过',0,0,3);
        }
        if (SystemMessage::where('action',12)->value('text_message') == 1) {
            $text = '提现申请';
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
