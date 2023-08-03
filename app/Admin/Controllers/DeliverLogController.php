<?php

namespace App\Admin\Controllers;

use App\Admin\Repositories\DeliverLog;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class DeliverLogController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new DeliverLog(), function (Grid $grid) {
            $grid->column('id')->sortable();
            $grid->column('user_id');
            $grid->column('course_id');
            $grid->column('introduce');
            $grid->column('image');
            $grid->column('status');
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
        return Show::make($id, new DeliverLog(), function (Show $show) {
            $show->field('id');
            $show->field('user_id');
            $show->field('course_id');
            $show->field('introduce');
            $show->field('image');
            $show->field('status');
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
        return Form::make(new DeliverLog(), function (Form $form) {
            $form->display('id');
            $form->text('user_id');
            $form->text('course_id');
            $form->text('introduce');
            $form->text('image');
            $form->text('status');
        
            $form->display('created_at');
            $form->display('updated_at');
        });
    }
}
