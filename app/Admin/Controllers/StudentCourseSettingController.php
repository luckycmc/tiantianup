<?php

namespace App\Admin\Controllers;

use App\Admin\Repositories\CourseSetting;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class StudentCourseSettingController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new CourseSetting(), function (Grid $grid) {
            $grid->model()->where('role',1);
            $grid->column('id')->sortable();
            $grid->column('role');
            $grid->column('end_time');
            $grid->column('latest_end_time');
            $grid->column('course_end');
            $grid->column('deal_pay');
            $grid->column('confirm_course');
            $grid->column('is_can_look');
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
            $show->field('role');
            $show->field('end_time');
            $show->field('latest_end_time');
            $show->field('course_end');
            $show->field('deal_pay');
            $show->field('confirm_course');
            $show->field('is_can_look');
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
            $form->text('role');
            $form->text('end_time');
            $form->text('latest_end_time');
            $form->text('course_end');
            $form->text('deal_pay');
            $form->text('confirm_course');
            $form->text('is_can_look');
        
            $form->display('created_at');
            $form->display('updated_at');
        });
    }
}
