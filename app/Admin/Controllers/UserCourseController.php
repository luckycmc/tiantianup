<?php

namespace App\Admin\Controllers;

use App\Admin\Repositories\UserCourse;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class UserCourseController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new UserCourse(), function (Grid $grid) {
            $grid->column('id')->sortable();
            $grid->column('user_id');
            $grid->column('role');
            $grid->column('course_id');
            $grid->column('created_at');
            $grid->column('updated_at')->sortable();
        
            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('id');
        
            });
            $grid->export()->rows(function ($rows) {
                foreach ($rows as &$row) {
                    $arr = ['已结束','进行中','待开始','已拒绝','待审核'];
                    $type_arr = ['','邀新活动','教师注册活动','成交活动'];
                    $row['status'] = $arr[$row['status']];
                    $row['type'] = $type_arr[$row['type']];
                    $row['is_disabled'] = $row['is_disabled'] == 0 ? '否' : '是';
                }
                return $rows;
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
        return Show::make($id, new UserCourse(), function (Show $show) {
            $show->field('id');
            $show->field('user_id');
            $show->field('role');
            $show->field('course_id');
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
        return Form::make(new UserCourse(), function (Form $form) {
            $form->display('id');
            $form->text('user_id');
            $form->text('role');
            $form->text('course_id');
        
            $form->display('created_at');
            $form->display('updated_at');
        });
    }
}
