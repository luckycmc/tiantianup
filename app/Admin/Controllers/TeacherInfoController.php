<?php

namespace App\Admin\Controllers;

use App\Admin\Repositories\TeacherInfo;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class TeacherInfoController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new TeacherInfo(), function (Grid $grid) {
            $grid->column('id')->sortable();
            $grid->column('user_id');
            $grid->column('id_card_front');
            $grid->column('id_card_backend');
            $grid->column('picture');
            $grid->column('highest_education');
            $grid->column('graduate_school');
            $grid->column('speciality');
            $grid->column('graduate_cert');
            $grid->column('diploma');
            $grid->column('teacher_cert');
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
        return Show::make($id, new TeacherInfo(), function (Show $show) {
            $show->field('id');
            $show->field('user_id');
            $show->field('id_card_front');
            $show->field('id_card_backend');
            $show->field('picture');
            $show->field('highest_education');
            $show->field('graduate_school');
            $show->field('speciality');
            $show->field('graduate_cert');
            $show->field('diploma');
            $show->field('teacher_cert');
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
        return Form::make(new TeacherInfo(), function (Form $form) {
            $form->display('id');
            $form->text('user_id');
            $form->text('id_card_front');
            $form->text('id_card_backend');
            $form->text('picture');
            $form->text('highest_education');
            $form->text('graduate_school');
            $form->text('speciality');
            $form->text('graduate_cert');
            $form->text('diploma');
            $form->text('teacher_cert');
        
            $form->display('created_at');
            $form->display('updated_at');
        });
    }
}
