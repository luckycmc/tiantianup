<?php

namespace App\Admin\Actions\Grid;

use App\Models\Course;
use Dcat\Admin\Actions\Response;
use Dcat\Admin\Grid\RowAction;
use Dcat\Admin\Traits\HasPermissions;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

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
        $course_id = $this->getKey();
        $course_info = Course::find($course_id);
        $course_info->status = 1;
        $course_info->update();
        $redirect = '/';
        if ($course_info->adder_role == 0) {
            $redirect = '/intermediary_course';
        } else if ($course_info->role == 1 && $course_info->adder_role !== 0) {
            $redirect = '/student_course';
        } else if ($course_info->role == 3 && $course_info->adder_role !== 0) {
            $redirect = '/teacher_course';
        }


        return $this->response()
            ->success('操作成功')
            ->redirect($redirect);
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
