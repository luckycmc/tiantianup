<?php

namespace App\Admin\Controllers;

use App\Admin\Repositories\TeacherCareer;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class TeacherCareerController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new TeacherCareer(), function (Grid $grid) {
            $grid->column('id')->sortable();
            $grid->column('user_id');
            $grid->column('organization');
            $grid->column('subject');
            $grid->column('object');
            $grid->column('teaching_type');
            $grid->column('start_time');
            $grid->column('end_time');
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
        return Show::make($id, new TeacherCareer(), function (Show $show) {
            $show->field('id');
            $show->field('user_id');
            $show->field('organization');
            $show->field('subject');
            $show->field('object');
            $show->field('teaching_type');
            $show->field('start_time');
            $show->field('end_time');
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
        return Form::make(new TeacherCareer(), function (Form $form) {
            $form->display('id');
            $form->text('user_id');
            $form->text('organization');
            $form->text('subject');
            $form->text('object');
            $form->text('teaching_type');
            $form->text('start_time');
            $form->text('end_time');
        
            $form->display('created_at');
            $form->display('updated_at');
        });
    }
}
