<?php

namespace App\Admin\Forms;

use App\Models\User;
use Dcat\Admin\Contracts\LazyRenderable;
use Dcat\Admin\Traits\LazyWidget;
use Dcat\Admin\Widgets\Form;

class DisableTeacher extends Form implements LazyRenderable
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
        $user = User::find($id);
        $status = $input['status'] ?? 2;
        $time = $input['time'] ?? 0;
        $disable_type = $input['disable_type'] ?? 0;
        $user->status = $status;
        $user->time = $time;
        $user->disable_type = $disable_type;
        $user->update();

        return $this
            ->response()
            ->success('禁用成功')
            ->refresh();
    }

    /**
     * Build a form here.
     */
    public function form()
    {
        $this->radio('status','是否永久禁用')->default(0)->options([2 => '否', 3 => '是'])->when([2],function () {
            $this->number('time','禁用时长');
            $this->radio('disable_type','禁用类型')->options(['h' => '小时', 'd' => '天', 'm' => '月']);
        });
    }

    /**
     * The data of the form.
     *
     * @return array
     */
    /*public function default()
    {
        return [
            'name'  => 'John Doe',
            'email' => 'John.Doe@gmail.com',
        ];
    }*/
}
