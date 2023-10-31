<?php

namespace App\Admin\Actions\Grid;

use App\Models\Course;
use App\Models\Message;
use App\Models\SystemMessage;
use App\Models\User;
use Dcat\Admin\Actions\Response;
use Dcat\Admin\Grid\RowAction;
use Dcat\Admin\Traits\HasPermissions;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Overtrue\EasySms\EasySms;
use Overtrue\EasySms\Exceptions\Exception;
use Overtrue\EasySms\Exceptions\NoGatewayAvailableException;
use Overtrue\EasySms\PhoneNumber;

class VerifyCourse extends RowAction
{
    /**
     * @return string
     */
	protected $title = '通过';

    /**
     * Handle the action request.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function handle(Request $request)
    {
        $config = config('services.sms');
        $course_id = $this->getKey();
        $course_info = Course::find($course_id);
        $course_info->status = 1;
        $course_info->is_recommend = 1;
        $course_info->update();
        $user = User::find($course_info->adder_id);

        // 发送通知
        if (SystemMessage::where('action',8)->value('site_message') == 1) {
            (new Message())->saveMessage($user->id,0,'需求审核','需求审核通过',$course_id,0,3);
        }
        if (SystemMessage::where('action',8)->value('text_message') == 1) {
            $text = '需求';
            // 发送短信
            $easySms = new EasySms($config);
            try {
                $number = new PhoneNumber($user->mobile);
                $easySms->send($number,[
                    'content'  => "【添添向尚】恭喜您，您的".$text."已通过审核",
                ]);
            } catch (Exception|NoGatewayAvailableException $exception) {
                return $this->response()
                    ->error('操作失败')
                    ->refresh();
            }
        }
        /*if (SystemMessage::where('action',6)->value('official_account') == 1) {
            // 推送公众号消息
            if (!isset($user->official_open_id)) {
                get_official_openid($user->union_id);
            }
        }*/


        return $this->response()
            ->success('操作成功')
            ->refresh();
    }

    /**
	 * @return string|array|void
	 */
	public function confirm()
	{
		return ['确定要通过吗?'];
	}

    /**
     * @param Model|Authenticatable|HasPermissions|null $user
     *
     * @return bool
     */
    protected function authorize($user): bool
    {
        return true;
    }

    /**
     * @return array
     */
    protected function parameters()
    {
        return [];
    }
}
