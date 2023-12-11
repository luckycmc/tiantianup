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
        return Grid::make(new UserCourse(['user','course']), function (Grid $grid) {
            $grid->column('id')->sortable();
            $grid->column('user.number','用户编号');
            $grid->column('user.name','用户名称');
            $grid->column('role','用户类型')->using([1 => '学生', 2 => '家长', 3 => '教师', 4 => '机构']);
            $grid->column('user.organization.name','机构名称');
            $grid->column('course.number','课程编号');
            $grid->column('course.name','课程名称');
            $grid->column('status','报名单状态')->using([0 => '待支付', 1 => '已支付']);
            $grid->column('user.mobile','手机号');
            $grid->column('amount','服务费');
            $grid->column('area','省市区')->display(function () {
                if ($this->user) {
                    return $this->user->province->region_name.$this->user->city->region_name.$this->user->district->region_name;
                } else {
                    return null;
                }
            });
            $grid->column('created_at','报名时间');
            $grid->column('updated_at','支付时间')->sortable();
        
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
