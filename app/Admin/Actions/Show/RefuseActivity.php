<?php

namespace App\Admin\Actions\Show;

use App\Admin\Forms\RefuseActivity as RefuseActivityForm;
use Dcat\Admin\Actions\Response;
use Dcat\Admin\Show\AbstractTool;
use Dcat\Admin\Traits\HasPermissions;
use Dcat\Admin\Widgets\Modal;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class RefuseActivity extends AbstractTool
{
    /**
     * @return string
     */
	protected $title = '拒绝';

    public function render()
    {
        // 实例化表单类并传递自定义参数
        $form = RefuseActivityForm::make()->payload(['id' => $this->getKey()]);

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
    	// dump($this->getKey());

        return $this->response()
            ->success('Processed successfully.')
            ->redirect('/');
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
