<?php

namespace App\Admin\Controllers;

use App\Admin\Repositories\ActivityLog;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class ActivityLogController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new ActivityLog(), function (Grid $grid) {
            $grid->column('id')->sortable();
            $grid->column('user_id');
            $grid->column('username');
            $grid->column('number');
            $grid->column('first_child');
            $grid->column('second_child');
            $grid->column('activity_id');
            $grid->column('project');
            $grid->column('amount');
            $grid->column('description');
            $grid->column('role');
            $grid->column('type');
            $grid->column('deal_amount');
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
        return Show::make($id, new ActivityLog(), function (Show $show) {
            $show->field('id');
            $show->field('user_id');
            $show->field('username');
            $show->field('number');
            $show->field('first_child');
            $show->field('second_child');
            $show->field('activity_id');
            $show->field('project');
            $show->field('amount');
            $show->field('description');
            $show->field('role');
            $show->field('type');
            $show->field('deal_amount');
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
        return Form::make(new ActivityLog(), function (Form $form) {
            $form->display('id');
            $form->text('user_id');
            $form->text('username');
            $form->text('number');
            $form->text('first_child');
            $form->text('second_child');
            $form->text('activity_id');
            $form->text('project');
            $form->text('amount');
            $form->text('description');
            $form->text('role');
            $form->text('type');
            $form->text('deal_amount');
        
            $form->display('created_at');
            $form->display('updated_at');
        });
    }
}
