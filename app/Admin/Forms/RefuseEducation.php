<?php

namespace App\Admin\Forms;

use Dcat\Admin\Contracts\LazyRenderable;
use Dcat\Admin\Traits\LazyWidget;
use Dcat\Admin\Widgets\Form;

class RefuseEducation extends Form  implements LazyRenderable
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
        $reason = $input['reason'] ?? '';
        $course_info = Course::find($id);
        $course_info->status = 3;
        $course_info->reason = $reason;
        $course_info->update();

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
