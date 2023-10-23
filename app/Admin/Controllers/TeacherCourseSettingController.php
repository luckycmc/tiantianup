<?php

namespace App\Admin\Controllers;

use App\Admin\Repositories\CourseSetting;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class TeacherCourseSettingController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new CourseSetting(), function (Grid $grid) {
            $grid->model()->where('role',3);
            $grid->column('id')->sortable();
            $grid->column('end_time');
            $grid->column('latest_end_time');
            $grid->column('course_end');
            $grid->column('deal_pay');
            $grid->column('confirm_course');
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
        return Show::make($id, new CourseSetting(), function (Show $show) {
            $show->field('id');
            $show->field('end_time');
            $show->field('latest_end_time');
            $show->field('course_end');
            $show->field('deal_pay');
            $show->field('confirm_course');
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
        return Form::make(new CourseSetting(), function (Form $form) {
            $form->display('id');
            $form->hidden('role')->default(3);
            $form->number('end_time');
            $form->number('latest_end_time');
            $form->number('course_end');
            $form->number('deal_pay');
            $form->number('confirm_course');
        
            $form->display('created_at');
            $form->display('updated_at');
        });
    }
}
