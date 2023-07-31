<?php

namespace App\Admin\Controllers;

use App\Admin\Repositories\TeacherTag;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class TeacherTagController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new TeacherTag(), function (Grid $grid) {
            $grid->column('id')->sortable();
            $grid->column('user_id');
            $grid->column('tag');
            $grid->column('update_at');
            $grid->column('created_at');
        
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
        return Show::make($id, new TeacherTag(), function (Show $show) {
            $show->field('id');
            $show->field('user_id');
            $show->field('tag');
            $show->field('update_at');
            $show->field('created_at');
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Form::make(new TeacherTag(), function (Form $form) {
            $form->display('id');
            $form->text('user_id');
            $form->text('tag');
            $form->text('update_at');
            $form->text('created_at');
        });
    }
}
