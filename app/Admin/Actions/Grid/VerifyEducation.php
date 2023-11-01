<?php

namespace App\Admin\Actions\Grid;

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
        $teacher_id = $this->getKey();
        $teacher_info = TeacherEducation::find($teacher_id);
        $teacher_info->status = 1;
        $teacher_info->update();
        $tag = $teacher_info->highest_education;
        $tag_info = [
            'user_id' => $teacher_id,
            'tag' => $tag
        ];
        TeacherTag::updateOrCreate(['user_id' => $teacher_id,'tag' => $tag],$tag_info);

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
