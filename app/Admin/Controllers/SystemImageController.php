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
            $grid->column('login_bg')->image('',60,60);
            $grid->column('parent_bg')->image('',60,60);
            $grid->column('student_bg')->image('',60,60);
            $grid->column('teacher_bg')->image('',60,60);
            $grid->column('organ_bg')->image('',60,60);
            $grid->column('share_post')->image('',60,60);
            $grid->column('invite_register_bg')->image('',60,60);
            $grid->column('teacher_detail_top_bg')->image('',60,60);
            $grid->column('invite_organ_confirm_bg')->image('',60,60);
            $grid->column('invite_teacher_confirm_bg')->image('',60,60);
            $grid->column('intermediary_bg')->image('',60,60);
        
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
            $form->image('login_bg')->saveFullUrl()->saving(function ($value) {
                $arr = explode('?',$value);
                return $arr[0];
            });
            $form->image('parent_bg')->saveFullUrl()->saving(function ($value) {
                $arr = explode('?',$value);
                return $arr[0];
            });;
            $form->image('student_bg')->saveFullUrl()->saving(function ($value) {
                $arr = explode('?',$value);
                return $arr[0];
            });;
            $form->image('teacher_bg')->saveFullUrl()->saving(function ($value) {
                $arr = explode('?',$value);
                return $arr[0];
            });;
            $form->image('organ_bg')->saveFullUrl()->saving(function ($value) {
                $arr = explode('?',$value);
                return $arr[0];
            });;
            $form->image('share_post')->saveFullUrl()->saving(function ($value) {
                $arr = explode('?',$value);
                return $arr[0];
            });;
            $form->image('invite_register_bg')->saveFullUrl()->saving(function ($value) {
                $arr = explode('?',$value);
                return $arr[0];
            });;
            $form->image('teacher_detail_top_bg')->saveFullUrl()->saving(function ($value) {
                $arr = explode('?',$value);
                return $arr[0];
            });;
            $form->image('invite_organ_confirm_bg')->saveFullUrl()->saving(function ($value) {
                $arr = explode('?',$value);
                return $arr[0];
            });;
            $form->image('invite_teacher_confirm_bg')->saveFullUrl()->saving(function ($value) {
                $arr = explode('?',$value);
                return $arr[0];
            });;
            $form->image('intermediary_bg')->saveFullUrl()->saving(function ($value) {
                $arr = explode('?',$value);
                return $arr[0];
            });;
        });
    }
}
