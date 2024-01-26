<?php

namespace App\Admin\Actions\Grid;

use App\Jobs\UpdateCourse;
use App\Models\Course;
use App\Models\CourseSetting;
use App\Models\Message;
use App\Models\Region;
use App\Models\SystemMessage;
use App\Models\User;
use Carbon\Carbon;
use Dcat\Admin\Actions\Response;
use Dcat\Admin\Admin;
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
        $course_info->is_on = 1;
        $course_info->is_recommend = 1;
        $course_info->reason = null;
        $course_info->reviewer = Admin::user()->name;
        $course_info->end_time = Carbon::now()->setTime(23,59,59)->addDays($course_info->valid_time);
        if ($course_info->adder_role == 0) {
            $province = Region::where('id',$course_info->province)->value('region_name');
            $city = Region::where('id',$course_info->city)->value('region_name');
            $district = Region::where('id',$course_info->district)->value('region_name');
            if ($course_info->method !== '线上') {
                $long_lat = get_long_lat($province,$city,$district,$course_info->address);
                $course_info->longitude = $long_lat[0];
                $course_info->latitude = $long_lat[1];
            }
        }
        $course_info->update();
        $user = User::find($course_info->adder_id);

        // 发送通知
        if ($course_info->adder_role !== 0) {
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
                        'content'  => "【添添学】恭喜您，您的".$text."已通过审核",
                    ]);
                } catch (Exception|NoGatewayAvailableException $exception) {
                    return $this->response()
                        ->error('操作失败')
                        ->refresh();
                }
            }
        } else {
            if (!$course_info->number) {
                $course_info->number = new_create_course_number($course_id,$course_info->method,0,3);
                $course_info->update();
            }
        }
        // 放入延时队列
        if ($course_info->adder_role == 0) {
            $role = 0;
        } else {
            if ($course_info->role == 1) {
                $role = 1;
            } else {
                $role = 3;
            }
        }
        $days = CourseSetting::where('role',$role)->orderByDesc('created_at')->first();
        // $days->latest_end_time)
        UpdateCourse::dispatch($course_info)->delay(now()->addMinute())->onQueue('update_course');

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
