<?php

namespace App\Admin\Forms;

use App\Models\Course;
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

class RefuseCourse extends Form implements LazyRenderable
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
        $course_info = Course::find($id);
        $course_info->status = 3;
        $course_info->reason = $reason;
        $course_info->reviewer = Admin::user()->id;
        $course_info->update();

        $user = User::find($course_info->adder_id);
        if ($course_info->adder_role !== 0) {
            // 发送通知
            if (SystemMessage::where('action',8)->value('site_message') == 1) {
                (new Message())->saveMessage($user->id,0,'需求审核','很抱歉，您的需求审核未通过',$id,0,3);
            }
            if (SystemMessage::where('action',8)->value('text_message') == 1) {
                $text = '需求';
                // 发送短信
                $easySms = new EasySms($config);
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
