<?php

namespace App\Admin\Controllers;

use App\Admin\Repositories\Withdraw;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class WithdrawController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new Withdraw(), function (Grid $grid) {
            $grid->column('id','提现id')->sortable();
            $grid->column('username','用户名');
            $grid->column('role','用户类型')->using([1 => '学生',2 => '家长',3 => '教师',4 => '机构']);
            $grid->column('type','提现类型')->using([1 => '支付宝',2 => '微信', 3 => '银行卡']);
            $grid->column('mobile');
            $grid->column('status','状态')->using([0 => '审核中',1 => '审核未通过', 2 => '打款中', 3 => '已打款']);
            $grid->column('amount');
            $grid->column('account');
            $grid->column('created_at','申请时间');
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
        return Show::make($id, new Withdraw(), function (Show $show) {
            $show->field('id');
            $show->field('user_id');
            $show->field('amount');
            $show->field('type');
            $show->field('username');
            $show->field('mobile');
            $show->field('status');
            $show->field('reason');
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
        return Form::make(new Withdraw(), function (Form $form) {
            $form->display('id');
            $form->text('user_id');
            $form->text('amount');
            $form->text('type');
            $form->text('username');
            $form->text('mobile');
            $form->text('status');
            $form->text('reason');
        
            $form->display('created_at');
            $form->display('updated_at');
        });
    }
}
