<?php

namespace App\Admin\Forms;

use App\Models\User;
use App\Models\Withdraw;
use Dcat\Admin\Contracts\LazyRenderable;
use Dcat\Admin\Traits\LazyWidget;
use Dcat\Admin\Widgets\Form;

class RefuseWithdraw extends Form implements LazyRenderable
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
        $info = Withdraw::find($id);
        $info->status = 1;
        $info->reason = $reason;
        $info->update();
        // 用户
        $user = User::find($info->user_id);
        $user->withdraw_balance += $info->amount;
        $user->update();

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
