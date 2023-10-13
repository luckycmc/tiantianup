<?php

namespace App\Admin\Controllers;

use App\Admin\Repositories\SystemImage;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class SystemImageController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new SystemImage(), function (Grid $grid) {
            $grid->column('id')->sortable();
            $grid->column('login_bg');
            $grid->column('parent_bg');
            $grid->column('student_bg');
            $grid->column('teacher_bg');
            $grid->column('organ_bg');
            $grid->column('share_post');
            $grid->column('invite_register_bg');
            $grid->column('teacher_detail_top_bg');
            $grid->column('invite_organ_confirm_bg');
            $grid->column('invite_teacher_confirm_bg');
            $grid->column('intermediary_bg');
        
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
        return Show::make($id, new SystemImage(), function (Show $show) {
            $show->field('id');
            $show->field('login_bg');
            $show->field('parent_bg');
            $show->field('student_bg');
            $show->field('teacher_bg');
            $show->field('organ_bg');
            $show->field('share_post');
            $show->field('invite_register_bg');
            $show->field('teacher_detail_top_bg');
            $show->field('invite_organ_confirm_bg');
            $show->field('invite_teacher_confirm_bg');
            $show->field('intermediary_bg');
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Form::make(new SystemImage(), function (Form $form) {
            $form->display('id');
            $form->text('login_bg');
            $form->text('parent_bg');
            $form->text('student_bg');
            $form->text('teacher_bg');
            $form->text('organ_bg');
            $form->text('share_post');
            $form->text('invite_register_bg');
            $form->text('teacher_detail_top_bg');
            $form->text('invite_organ_confirm_bg');
            $form->text('invite_teacher_confirm_bg');
            $form->text('intermediary_bg');
        });
    }
}
