<?php

namespace App\Admin\Forms;

use App\Models\Activity;
use Dcat\Admin\Contracts\LazyRenderable;
use Dcat\Admin\Traits\LazyWidget;
use Dcat\Admin\Widgets\Form;

class RefuseActivity extends Form implements LazyRenderable
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
        $activity = Activity::find($id);
        if ($activity->status !== 4) {
            return $this
                ->response()
                ->error('操作失败')
                ->refresh();
        }
        $activity->status = 3;
        $activity->reason = $reason;
        $activity->update();

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
