<?php

namespace App\Admin\Controllers;

use App\Admin\Repositories\TeacherEducation;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class TeacherEducationController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new TeacherEducation(), function (Grid $grid) {
            $grid->column('id')->sortable();
            $grid->column('user_id');
            $grid->column('highest_education');
            $grid->column('education_id');
            $grid->column('graduate_school');
            $grid->column('speciality');
            $grid->column('graduate_cert');
            $grid->column('diploma');
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
        return Show::make($id, new TeacherEducation(), function (Show $show) {
            $show->field('id');
            $show->field('user_id');
            $show->field('highest_education');
            $show->field('education_id');
            $show->field('graduate_school');
            $show->field('speciality');
            $show->field('graduate_cert');
            $show->field('diploma');
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
        return Form::make(new TeacherEducation(), function (Form $form) {
            $form->display('id');
            $form->text('user_id');
            $form->text('highest_education');
            $form->text('education_id');
            $form->text('graduate_school');
            $form->text('speciality');
            $form->text('graduate_cert');
            $form->text('diploma');
            $form->text('status');
        
            $form->display('created_at');
            $form->display('updated_at');
        });
    }
}
