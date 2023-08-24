<?php

namespace App\Admin\Controllers;

use App\Admin\Repositories\UserCourse;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class UserCourseController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new UserCourse(), function (Grid $grid) {
            $grid->column('id')->sortable();
            $grid->column('user_id');
            $grid->column('role');
            $grid->column('course_id');
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
        return Show::make($id, new UserCourse(), function (Show $show) {
            $show->field('id');
            $show->field('user_id');
            $show->field('role');
            $show->field('course_id');
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
        return Form::make(new UserCourse(), function (Form $form) {
            $form->display('id');
            $form->text('user_id');
            $form->text('role');
            $form->text('course_id');
        
            $form->display('created_at');
            $form->display('updated_at');
        });
    }
}
