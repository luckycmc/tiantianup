<?php

namespace App\Admin\Actions\Grid;

use App\Models\User;
use Carbon\Carbon;
use Dcat\Admin\Actions\Response;
use Dcat\Admin\Grid\RowAction;
use Dcat\Admin\Traits\HasPermissions;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class CancelRecommendTeacher extends RowAction
{
    /**
     * @return string
     */
	protected $title = '取消推荐';

    /**
     * Handle the action request.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function handle(Request $request)
    {
        $id = $this->getKey();
        $teacher_info = User::find($id);
        if (!$teacher_info) {
            return $this->response()->error('老师不存在');
        }
        $teacher_info->is_recommend = 1;
        $teacher_info->recommend_time = Carbon::now();
        $teacher_info->update();

        return $this->response()
            ->success('取消推荐成功')
            ->refresh();
    }

    /**
	 * @return string|array|void
	 */
	public function confirm()
	{
		// return ['Confirm?', 'contents'];
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
