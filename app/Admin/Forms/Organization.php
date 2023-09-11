<?php

namespace App\Admin\Forms;

use Dcat\Admin\Widgets\Form;
use Dcat\Admin\Widgets\Widget;

class Organization extends Form
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
        // dump($input);

        // return $this->response()->error('Your error message.');

        return parent::update($input);
    }

    /**
     * Build a form here.
     */
    public function form(Widget $form)
    {
        $statuses = [
            1 => '正常',
            2 => '禁用',
            3 => '锁定',
        ];

        $form->select('status', '状态')->options($statuses);
    }

    /**
     * The data of the form.
     *
     * @return array
     */
    public function default()
    {
        return [
            'name'  => 'John Doe',
            'email' => 'John.Doe@gmail.com',
        ];
    }
}
