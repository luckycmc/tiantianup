<?php

namespace App\Admin\Actions\Grid;

use App\Models\Message;
use App\Models\SystemMessage;
use App\Models\TeacherEducation;
use App\Models\TeacherTag;
use App\Models\User;
use Carbon\Carbon;
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

class VerifyEducation extends RowAction
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
        $teacher_id = $this->getKey();
        $teacher_info = TeacherEducation::find($teacher_id);
        $teacher_info->status = 1;
        $teacher_info->reason = null;
        $teacher_info->update();
        $user = User::find($teacher_info->user_id);
        $tag = $teacher_info->highest_education;
        $tag_info = [
            'user_id' => $teacher_id,
            'tag' => $tag,
            'type' => 0
        ];
        TeacherTag::updateOrCreate(['user_id' => $teacher_id,'tag' => $tag],$tag_info);
        // 发送通知
        if (SystemMessage::where('action',6)->value('site_message') == 1) {
            (new Message())->saveMessage($teacher_info->user_id,0,'教育经历','教育经历审核通过',0,0,3);
        }
        if (SystemMessage::where('action',6)->value('text_message') == 1) {
            $text = '教育经历';
            // 发送短信
            $easySms = new EasySms($config);
            try {
                $number = new PhoneNumber($user->mobile);
                $easySms->send($number,[
                    'content'  => "【添添学】恭喜您，您的".$text."已通过审核",
                ]);
            } catch (Exception|NoGatewayAvailableException $exception) {
                return $this->error($exception->getResults());
            }
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
