<?php

namespace App\Admin\Actions\Show;

use App\Models\Activity;
use Dcat\Admin\Actions\Response;
use Dcat\Admin\Show\AbstractTool;
use Dcat\Admin\Traits\HasPermissions;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class VerifyActivity extends AbstractTool
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
        $id = $this->getKey();
        $activity = Activity::find($id);
        if ($activity->status !== 4) {
            return $this
                ->response()
                ->error('操作失败')
                ->refresh();
        }
        $activity->status = 1;
        $activity->update();

        return $this->response()
            ->success('操作成功')
            ->refresh();
    }

    /**
     * @return string|void
     */
    protected function href()
    {
        // return admin_url('auth/users');
    }

    /**
	 * @return string|array|void
	 */
	public function confirm()
	{
        return ['确定通过审核吗？'];
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
