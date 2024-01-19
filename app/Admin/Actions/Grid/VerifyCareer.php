<?php

namespace App\Admin\Actions\Grid;

use App\Models\Activity;
use App\Models\Message;
use App\Models\SystemMessage;
use App\Models\TeacherCareer;
use App\Models\TeacherTag;
use App\Models\User;
use Carbon\Carbon;
use Dcat\Admin\Actions\Response;
use Dcat\Admin\Grid\RowAction;
use Dcat\Admin\Traits\HasPermissions;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Overtrue\EasySms\EasySms;
use Overtrue\EasySms\Exceptions\Exception;
use Overtrue\EasySms\Exceptions\NoGatewayAvailableException;
use Overtrue\EasySms\PhoneNumber;

class VerifyCareer extends RowAction
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
        $teacher_info = TeacherCareer::find($teacher_id);
        $teacher_info->status = 1;
        $teacher_info->reason = null;
        // 查询奖励
        // $reward = get_reward(2,3);
        // $amount = $reward->teacher_real_auth_reward;
        $user = User::find($teacher_info->user_id);
        /*$user->withdraw_balance += $amount;
        $user->total_income += $amount;*/
        $start_time = Carbon::parse($teacher_info->start_time);
        $end_time = Carbon::parse($teacher_info->end_time);
        $teaching_year = $start_time->diffInYears($end_time);
        $user->teaching_year += $teaching_year;
        $tag = $user->teaching_year.'年教学经验';
        $tag_info = [
            'user_id' => $user->id,
            'tag' => $tag,
            'type' => 1,
        ];
        // 删除所有教学经历标签
        TeacherTag::where(['user_id' => $user->id,'type' => 1])->delete();
        // 保存日志
        DB::transaction(function () use ($teacher_info,$user,$tag_info) {
            $teacher_info->update();
            $user->update();
            TeacherTag::updateOrCreate(['user_id' => $user->id,'tag' => $tag_info['tag']],$tag_info);
        });

        // 发送通知
        if (SystemMessage::where('action',6)->value('site_message') == 1) {
            (new Message())->saveMessage($teacher_info->user_id,0,'教学经历','教学经历审核通过',0,0,3);
        }
        if (SystemMessage::where('action',6)->value('text_message') == 1) {
            $text = '教学经历';
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

        // 当前时间
        $current = Carbon::now()->format('Y-m-d');
        // 查看是否有注册活动
        $teacher_activity = Activity::where(['status' => 1,'type' => 2])->where('start_time', '<=', $current)
            ->where('end_time', '>=', $current)->first();
        if ($teacher_activity) {
            // 查询是否已获得奖励
            $is_reward = \App\Models\ActivityLog::where(['user_id' => $user->id,'activity_id' => $teacher_activity->id,'description' => '教学经历审核通过'])->exists();
            if ($teacher_activity && !$is_reward) {
                teacher_activity_log($teacher_info->user_id,'teacher_career_reward','教学经历','教学经历审核通过',$teacher_activity);
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
