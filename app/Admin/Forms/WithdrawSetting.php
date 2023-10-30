<?php

namespace App\Admin\Forms;

use App\Models\BaseInformation;
use Dcat\Admin\Widgets\Form;

class WithdrawSetting extends Form
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
        $withdraw_min = $input['withdraw_min'] ?? null;
        $withdraw_commission = $input['withdraw_commission'] ?? null;
        $withdraw_pay_time = $input['withdraw_pay_time'] ?? null;
        $base_information = BaseInformation::first();
        $base_information->withdraw_min = $withdraw_min;
        $base_information->withdraw_commission = $withdraw_commission;
        $base_information->withdraw_pay_time = $withdraw_pay_time;
        $base_information->update();


        return $this
				->response()
				->success('配置成功')
				->refresh();
    }

    /**
     * Build a form here.
     */
    public function form()
    {
        $this->number('withdraw_min','最低提现金额')->required();
        $this->number('withdraw_commission','提现手续费')->required();
        $this->number('withdraw_pay_time','预计打款时间')->required();
    }

    /**
     * The data of the form.
     *
     * @return array
     */
    public function default()
    {
        $base_information = BaseInformation::first();
        $withdraw_min = $base_information->withdraw_min;
        $withdraw_commission = $base_information->withdraw_commission;
        $withdraw_pay_time = $base_information->withdraw_pay_time;
        return [
            'withdraw_min'  => $withdraw_min,
            'withdraw_commission' => $withdraw_commission,
            'withdraw_pay_time' => $withdraw_pay_time,
        ];
    }
}
