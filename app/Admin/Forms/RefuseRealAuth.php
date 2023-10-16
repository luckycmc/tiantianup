<?php

namespace App\Admin\Forms;

use App\Models\TeacherInfo;
use Dcat\Admin\Widgets\Form;

class RefuseRealAuth extends Form
{
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
        $reason = $input['reason'] ?? '';
        $teacher_info = TeacherInfo::find($id);
        $teacher_info->status = 3;
        $teacher_info->reason = $reason;
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
        $this->text('reason','拒绝原因')->required();
    }

    /**
     * The data of the form.
     *
     * @return array
     */
    public function default()
    {
        return [
            'reason'  => '',
        ];
    }
}
