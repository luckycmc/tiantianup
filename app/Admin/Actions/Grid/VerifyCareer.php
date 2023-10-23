<?php

namespace App\Admin\Actions\Grid;

use App\Models\TeacherCareer;
use App\Models\User;
use Carbon\Carbon;
use Dcat\Admin\Actions\Response;
use Dcat\Admin\Grid\RowAction;
use Dcat\Admin\Traits\HasPermissions;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        $teacher_id = $this->getKey();
        $teacher_info = TeacherCareer::find($teacher_id);
        $teacher_info->status = 1;
        // 查询奖励
        $reward = get_reward(2,3);
        $amount = $reward->teacher_real_auth_reward;
        $user = User::find($teacher_info->user_id);
        $user->withdraw_balance += $amount;
        $user->total_income += $amount;
        $bill_log = [
            'user_id' => $teacher_info->user_id,
            'amount' => $amount,
            'type' => 9,
            'description' => '教学经历审核通过',
            'created_at' => Carbon::now()
        ];
        // 保存日志
        DB::transaction(function () use ($bill_log,$teacher_info,$user) {
            $teacher_info->update();
            $user->update();
            DB::table('bills')->insert($bill_log);
        });

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
