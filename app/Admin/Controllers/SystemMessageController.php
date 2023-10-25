<?php

namespace App\Admin\Controllers;

use App\Admin\Repositories\SystemMessage;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class SystemMessageController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new SystemMessage(), function (Grid $grid) {
            $grid->column('id')->sortable();
            $grid->column('name');
            $grid->column('action')->using([
                0 => '机构入驻',
                1 => '审核机构入驻',
                2 => '机构端修改机构资料',
                3 => '教师实名认证',
                4 => '审核教师实名认证',
                5 => '教师资格证更新',
                6 => '审核教师资格证',
                7 => '发布需求',
                8 => '审核需求',
                9 => '教师投递',
                10 => '选中教师',
                11 => '提现申请',
                12 => '提现申请结果',
                13 => '满足奖励条件获得佣金',
                14 => '教师确认需求',
                15 => '教师成交服务费距到期时间小于10分钟',
                16 => '学生、家长报名机构的找学员需求',
                17 => '距离需求到期前提醒',
                18 => '教师支付成交服务费后，提醒教师及时确认需求',
                19 => '活动开始后给活动对象发送消息提醒'
            ]);
            $grid->column('site_message')->radio([0 => '关', 1 => '开']);
            $grid->column('text_message')->radio([0 => '关', 1 => '开']);
            $grid->column('official_account')->radio([0 => '关', 1 => '开']);
            $grid->column('object');
            $grid->column('created_at');
            $grid->column('updated_at')->sortable();
        
            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('id');
        
            });
        });
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     *
     * @return Show
     */
    protected function detail($id)
    {
        return Show::make($id, new SystemMessage(), function (Show $show) {
            $show->field('id');
            $show->field('name');
            $show->field('action');
            $show->field('site_message');
            $show->field('text_message');
            $show->field('official_account');
            $show->field('object');
            $show->field('created_at');
            $show->field('updated_at');
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Form::make(new SystemMessage(), function (Form $form) {
            $form->display('id');
            $form->text('name');
            $form->select('action')->options([
                0 => '机构入驻',
                1 => '审核机构入驻',
                2 => '机构端修改机构资料',
                3 => '教师实名认证',
                4 => '审核教师实名认证',
                5 => '教师资格证更新',
                6 => '审核教师资格证',
                7 => '发布需求',
                8 => '审核需求',
                9 => '教师投递',
                10 => '选中教师',
                11 => '提现申请',
                12 => '提现申请结果',
                13 => '满足奖励条件获得佣金',
                14 => '教师确认需求',
                15 => '教师成交服务费距到期时间小于10分钟',
                16 => '学生、家长报名机构的找学员需求',
                17 => '距离需求到期前提醒',
                18 => '教师支付成交服务费后，提醒教师及时确认需求',
                19 => '活动开始后给活动对象发送消息提醒'
            ]);
            $form->radio('site_message')->options([0 => '关', 1 => '开'])->default(0);
            $form->radio('text_message')->options([0 => '关', 1 => '开'])->default(0);
            $form->radio('official_account')->options([0 => '关', 1 => '开'])->default(0);
            $form->text('object');

            $form->display('created_at');
            $form->display('updated_at');
        });
    }
}
