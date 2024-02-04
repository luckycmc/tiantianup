<?php

namespace App\Admin\Actions\Grid;

use App\Models\Course;
use Carbon\Carbon;
use Dcat\Admin\Actions\Response;
use Dcat\Admin\Grid\RowAction;
use Dcat\Admin\Traits\HasPermissions;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class CancelRecommendCourse extends RowAction
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
        $course_info = Course::find($id);
        if (!$course_info) {
            return $this->response()->error('需求不存在');
        }
        $course_info->is_recommend = 0;
        $course_info->recommend_time = Carbon::now();
        $course_info->update();

        return $this->response()
            ->success('取消推荐成功')
            ->refresh();
    }

    /**
	 * @return string|array|void
	 */
	public function confirm()
	{
        return ['确定取消推荐吗？'];
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
