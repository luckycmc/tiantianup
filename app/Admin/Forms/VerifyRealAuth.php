<?php

namespace App\Admin\Forms;

use App\Models\Message;
use App\Models\SystemMessage;
use App\Models\TeacherInfo;
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
        $teacher_info->id_card_no = $id_card_no;
        $teacher_info->real_name = $real_name;
        // 查询奖励
        $reward = get_reward(2,3);
        $amount = $reward->teacher_real_auth_reward;
        $user = User::find($teacher_info->user_id);
        $user->withdraw_balance += $amount;
        $user->total_income += $amount;
        $bill_log = [
            'user_id' => $teacher_info->user_id,
            'amount' => $amount,
            'type' => 6,
            'description' => '实名认证审核通过',
            'created_at' => Carbon::now()
        ];
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
                    'content'  => "【添添向尚】恭喜您，您的".$text."已通过审核",
                ]);
            } catch (Exception|NoGatewayAvailableException $exception) {
                return $this->error($exception->getResults());
            }
        }
        // 保存日志
        DB::transaction(function () use ($bill_log,$teacher_info,$user) {
            $teacher_info->update();
            $user->update();
            DB::table('bills')->insert($bill_log);
        });

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
