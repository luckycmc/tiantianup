<?php

namespace App\Admin\Controllers;

use App\Admin\Repositories\ParentCourse;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class ParentCourseController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new ParentCourse(), function (Grid $grid) {
            $grid->column('id')->sortable();
            $grid->column('user_id');
            $grid->column('name');
            $grid->column('gender');
            $grid->column('student');
            $grid->column('grade');
            $grid->column('subject');
            $grid->column('type');
            $grid->column('class_type');
            $grid->column('class_time');
            $grid->column('duration');
            $grid->column('class_price_min');
            $grid->column('class_price_max');
            $grid->column('class_number');
            $grid->column('end_time');
            $grid->column('content');
            $grid->column('notes');
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
        return Show::make($id, new ParentCourse(), function (Show $show) {
            $show->field('id');
            $show->field('user_id');
            $show->field('name');
            $show->field('gender');
            $show->field('student');
            $show->field('grade');
            $show->field('subject');
            $show->field('type');
            $show->field('class_type');
            $show->field('class_time');
            $show->field('duration');
            $show->field('class_price_min');
            $show->field('class_price_max');
            $show->field('class_number');
            $show->field('end_time');
            $show->field('content');
            $show->field('notes');
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
        return Form::make(new ParentCourse(), function (Form $form) {
            $form->display('id');
            $form->text('user_id');
            $form->text('name');
            $form->text('gender');
            $form->text('student');
            $form->text('grade');
            $form->text('subject');
            $form->text('type');
            $form->text('class_type');
            $form->text('class_time');
            $form->text('duration');
            $form->text('class_price_min');
            $form->text('class_price_max');
            $form->text('class_number');
            $form->text('end_time');
            $form->text('content');
            $form->text('notes');
            $form->text('status');
        
            $form->display('created_at');
            $form->display('updated_at');
        });
    }
}
