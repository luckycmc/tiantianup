<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\Grid\DisableActivity;
use App\Admin\Actions\Grid\UnDisableActivity;
use App\Admin\Repositories\Activity;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class TeacherRegisterActivityController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new Activity(), function (Grid $grid) {
            $grid->model()->where('type',2);
            $grid->column('id','活动id')->sortable();
            $grid->column('name','活动名称');
            $grid->column('status','活动状态')->using([0 => '已结束',1 => '进行中',2 => '待开始',3 => '已拒绝', 4 => '待审核', 5 => '禁用']);
            $grid->column('start_time','开始时间');
            $grid->column('end_time','结束时间');
            $grid->column('object','活动对象');
            $grid->column('type','活动类型')->using([1 => '邀新活动',2 => '教师注册活动',3 => '成交活动']);
            $grid->column('adder.name','创建人');
            $grid->column('created_at','创建时间');

            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('id');

            });
            $grid->actions(function ($actions) {
                $status = $actions->row->status;
                if ($status !== 5) {
                    $actions->append(new DisableActivity());
                } else {
                    $actions->append(new UnDisableActivity());
                }
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
        return Show::make($id, new Activity(), function (Show $show) {
            $show->field('id');
            $show->field('name');
            $show->field('image');
            $show->field('object');
            $show->field('type');
            $show->field('description');
            $show->field('reward');
            $show->field('introduction');
            $show->field('start_time');
            $show->field('end_time');
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
        return Form::make(new Activity(), function (Form $form) {
            $form->display('id');
            $form->text('name');
            $form->image('image','图片')->saveFullUrl()->saving(function ($value) {
                $arr = explode('?',$value);
                return $arr[0];
            });
            $form->radio('object','对象')->options(['教师' => '教师'])->default('教师');
            $form->hidden('type','类型')->default(2);
            $form->text('description','活动描述');
            $form->number('teacher_real_auth_reward','实名认证奖励');
            $form->number('teacher_cert_reward','资格证书奖励');
            $form->number('teacher_career_reward','教学经历奖励');
            $form->number('teacher_image_reward','教师风采/客户见证奖励');
            $form->text('introduction','介绍');
            $form->dateRange('start_time','end_time','活动时间');
            $form->select('status','状态')->options([0 => '已结束',1 => '进行中',2 => '待开始',3 => '已拒绝', 4 => '待审核', 5 => '禁用']);

            $form->display('created_at');
            $form->display('updated_at');
        });
    }
}
