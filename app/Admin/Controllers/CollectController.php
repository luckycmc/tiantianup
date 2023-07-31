<?php

namespace App\Admin\Controllers;

use App\Admin\Repositories\Collect;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class CollectController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new Collect(), function (Grid $grid) {
            $grid->column('id')->sortable();
            $grid->column('user_id');
            $grid->column('course_id');
            $grid->column('teacher_id');
            $grid->column('type');
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
        return Show::make($id, new Collect(), function (Show $show) {
            $show->field('id');
            $show->field('user_id');
            $show->field('course_id');
            $show->field('teacher_id');
            $show->field('type');
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
        return Form::make(new Collect(), function (Form $form) {
            $form->display('id');
            $form->text('user_id');
            $form->text('course_id');
            $form->text('teacher_id');
            $form->text('type');
        
            $form->display('created_at');
            $form->display('updated_at');
        });
    }
}
