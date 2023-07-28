<?php

namespace App\Admin\Controllers;

use App\Admin\Repositories\ParentStudent;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class ParentStudentController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new ParentStudent(), function (Grid $grid) {
            $grid->column('id')->sortable();
            $grid->column('user_id');
            $grid->column('name');
            $grid->column('mobile');
            $grid->column('gender');
            $grid->column('school');
            $grid->column('birthday');
            $grid->column('grade');
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
        return Show::make($id, new ParentStudent(), function (Show $show) {
            $show->field('id');
            $show->field('user_id');
            $show->field('name');
            $show->field('mobile');
            $show->field('gender');
            $show->field('school');
            $show->field('birthday');
            $show->field('grade');
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
        return Form::make(new ParentStudent(), function (Form $form) {
            $form->display('id');
            $form->text('user_id');
            $form->text('name');
            $form->text('mobile');
            $form->text('gender');
            $form->text('school');
            $form->text('birthday');
            $form->text('grade');
        
            $form->display('created_at');
            $form->display('updated_at');
        });
    }
}
