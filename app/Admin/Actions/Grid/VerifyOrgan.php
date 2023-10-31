<?php

namespace App\Admin\Actions\Grid;

use App\Models\Message;
use App\Models\Organization;
use App\Models\SystemMessage;
use App\Models\User;
use Dcat\Admin\Actions\Response;
use Dcat\Admin\Grid\RowAction;
use Dcat\Admin\Traits\HasPermissions;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Overtrue\EasySms\EasySms;
use Overtrue\EasySms\Exceptions\Exception;
use Overtrue\EasySms\Exceptions\NoGatewayAvailableException;
use Overtrue\EasySms\PhoneNumber;

class VerifyOrgan extends RowAction
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
        $id = $this->getKey();
        $organ_info = Organization::find($id);
        $organ_info->status = 1;
        $organ_info->update();
        // 发送通知
        if (SystemMessage::where('action',1)->value('site_message') == 1) {
            (new Message())->saveMessage($organ_info->user_id,0,'机构入驻','机构入驻审核通过',0,0,3);
        }
        if (SystemMessage::where('action',1)->value('text_message') == 1) {
            $user = User::find($organ_info->user_id);
            Log::info('mobile: '.$user->mobile);
            $text = '机构入驻';
            /*// 发送短信
            $easySms = new EasySms($config);
            try {
                $number = new PhoneNumber($user->mobile);
                $easySms->send($number,[
                    'content'  => "【添添向尚】恭喜您，您的".$text."已通过审核",
                ]);
            } catch (Exception|NoGatewayAvailableException $exception) {
                return $this->error($exception->getResults());
            }*/
        }

        return $this->response()
            ->success('操作成功')
            ->refresh();
    }

    /**
	 * @return string|array|void
	 */
	public function confirm()
	{
		return ['确定通过吗？'];
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
