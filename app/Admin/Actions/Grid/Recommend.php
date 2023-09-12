<?php

namespace App\Admin\Actions\Grid;

use App\Models\User;
use Dcat\Admin\Actions\Response;
use Dcat\Admin\Grid\BatchAction;
use Dcat\Admin\Traits\HasPermissions;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class Recommend extends BatchAction
{
    /**
     * @return string
     */
    protected $action;

    public function __construct($title = null,$action = 1)
    {
        parent::__construct($title);
        $this->action = $action;
        $this->title = $title;
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
        // 获取选中的文章ID数组
        $keys = $this->getKey();
        // 获取请求参数
        $action = $request->get('action');
        foreach (User::find($keys) as $v) {
            $v->is_recommend = $action;
            $v->save();
        }
        return $this->response()->success('批量推荐成功')->refresh();
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
        return [
            'action' => $this->action,
        ];
    }
}
