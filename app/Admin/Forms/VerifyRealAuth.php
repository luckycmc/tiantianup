<?php

namespace App\Admin\Forms;

use App\Models\TeacherInfo;
use Dcat\Admin\Contracts\LazyRenderable;
use Dcat\Admin\Traits\LazyWidget;
use Dcat\Admin\Widgets\Form;

class VerifyRealAuth extends Form implements LazyRenderable
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
        $id_card_no = $input['id_card_no'] ?? '';
        $real_name = $input['real_name'] ?? '';
        $teacher_info = TeacherInfo::find($id);
        $teacher_info->status = 1;
        $teacher_info->id_card_no = $id_card_no;
        $teacher_info->real_name = $real_name;
        $teacher_info->update();

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
        $this->text('id_card_no','身份证号')->required();
        $this->text('real_name','真实姓名')->required();
    }

    /**
     * The data of the form.
     *
     * @return array
     */
    public function default()
    {
        return [
            'id_card_no'  => '',
            'real_name' => '',
        ];
    }
}
