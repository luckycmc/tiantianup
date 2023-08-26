<?php

namespace App\Admin\Controllers;

use App\Admin\Repositories\UserTeacherOrder;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class UserTeacherOrderController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new UserTeacherOrder(), function (Grid $grid) {
            $grid->column('id')->sortable();
            $grid->column('user_id');
            $grid->column('role');
            $grid->column('teacher_id');
            $grid->column('out_trade_no');
            $grid->column('amount');
            $grid->column('discount');
            $grid->column('status');
            $grid->column('pay_type');
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
        return Show::make($id, new UserTeacherOrder(), function (Show $show) {
            $show->field('id');
            $show->field('user_id');
            $show->field('role');
            $show->field('teacher_id');
            $show->field('out_trade_no');
            $show->field('amount');
            $show->field('discount');
            $show->field('status');
            $show->field('pay_type');
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
        return Form::make(new UserTeacherOrder(), function (Form $form) {
            $form->display('id');
            $form->text('user_id');
            $form->text('role');
            $form->text('teacher_id');
            $form->text('out_trade_no');
            $form->text('amount');
            $form->text('discount');
            $form->text('status');
            $form->text('pay_type');
        
            $form->display('created_at');
            $form->display('updated_at');
        });
    }
}
