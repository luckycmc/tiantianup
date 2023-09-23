<?php

namespace App\Admin\Controllers;

use App\Admin\Repositories\TeacherCert;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class TeacherCertController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new TeacherCert(), function (Grid $grid) {
            $grid->column('id')->sortable();
            $grid->column('teacher_id');
            $grid->column('teacher_cert');
            $grid->column('other_cert');
            $grid->column('honor_cert');
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
        return Show::make($id, new TeacherCert(), function (Show $show) {
            $show->field('id');
            $show->field('teacher_id');
            $show->field('teacher_cert');
            $show->field('other_cert');
            $show->field('honor_cert');
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
        return Form::make(new TeacherCert(), function (Form $form) {
            $form->display('id');
            $form->text('teacher_id');
            $form->text('teacher_cert');
            $form->text('other_cert');
            $form->text('honor_cert');
            $form->text('status');
        
            $form->display('created_at');
            $form->display('updated_at');
        });
    }
}
