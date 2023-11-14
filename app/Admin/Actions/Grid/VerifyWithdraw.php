<?php

namespace App\Admin\Actions\Grid;

use App\Models\Message;
use App\Models\SystemMessage;
use App\Models\User;
use App\Models\Withdraw;
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

class VerifyWithdraw extends RowAction
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
        $info = Withdraw::find($id);
        $info->status = 2;
        $info->update();
        $user = User::find($info->user_id);
        // 发送通知
        if (SystemMessage::where('action',12)->value('site_message') == 1) {
            (new Message())->saveMessage($user->id,0,'提现审核','提现审核通过',0,0,3);
        }
        if (SystemMessage::where('action',12)->value('text_message') == 1) {
            $text = '提现申请';
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
        return $this->response()->success('审核成功')->refresh();
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
