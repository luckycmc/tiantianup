<?php

namespace App\Admin\Actions\Grid;

use App\Admin\Forms\VerifyRealAuth as VerifyRealAuthForm;
use App\Models\TeacherInfo;
use App\Models\User;
use Dcat\Admin\Actions\Response;
use Dcat\Admin\Grid\RowAction;
use Dcat\Admin\Traits\HasPermissions;
use Dcat\Admin\Widgets\Modal;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class VerifyRealAuth extends RowAction
{
    /**
     * @return string
     */
	protected $title = '通过';

    public function render()
    {
        // 实例化表单类并传递自定义参数
        $form = VerifyRealAuthForm::make()->payload(['id' => $this->getKey()]);

        return Modal::make()
            ->lg()
            ->title($this->title)
            ->body($form)
            ->button($this->title);
    }

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
        $teacher_info = TeacherInfo::find($teacher_id);
        $teacher_info->status = 1;
        $teacher_info->update();

        $user_info = User::find($teacher_id);
        $user_info->is_real_auth = 1;
        $user_info->save();

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
