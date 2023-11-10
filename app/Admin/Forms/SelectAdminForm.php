<?php

namespace App\Admin\Forms;

use App\Models\SystemMessage;
use Dcat\Admin\Contracts\LazyRenderable;
use Dcat\Admin\Models\Administrator;
use Dcat\Admin\Traits\LazyWidget;
use Dcat\Admin\Widgets\Form;

class SelectAdminForm extends Form implements LazyRenderable
{
    use LazyWidget;
    /**
     * Handle the form request.
     *
     * @param array $input
     *
     * @return mixed
     */
    public function handle(array $input)
    {
        $id = $this->payload['id'] ?? null;
        $admin_id = $input['admin_id'] ?? 0;
        $admin = Administrator::find($admin_id);
        $message = SystemMessage::find($id);
        $message->admin_mobile = $admin->mobile;
        $message->update();

        return $this
				->response()
				->success('操作成功')
				->refresh();
    }

    /**
     * Build a form here.
     */
    public function form()
    {
        $this->select('admin_id')->options('/api/admin_users');
    }

    /**
     * The data of the form.
     *
     * @return array
     */
    public function default()
    {
        return [
        ];
    }
}
