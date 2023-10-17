<?php

namespace App\Admin\Controllers;

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
            $grid->column('status','活动状态')->using([0 => '已结束',1 => '进行中']);
            $grid->column('start_time','开始时间');
            $grid->column('end_time','结束时间');
            $grid->column('object','活动对象')->display(function ($object) {
                $arr = implode($object);
                if (count($arr) == 4) {
                    return '全部';
                }
            });
            $grid->column('type','活动类型')->using([1 => '邀新活动',2 => '教师注册活动',3 => '成交活动']);
            $grid->column('adder.name','创建人');
            $grid->column('created_at','创建时间');

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
            $form->checkbox('object','对象')->options(['学生' => '学生', '家长' => '家长', '教师' => '教师', '机构' => '机构'])->saving(function ($value) {
                return implode(',',$value);
            })->canCheckAll();
            $form->hidden('type','类型')->default(2);
            $form->text('description','介绍');
            $form->text('reward','奖励');
            $form->text('introduction','介绍');
            $form->dateRange('start_time','end_time','活动时间');
            $form->select('status','状态')->options([0 => '已结束',1 => '进行中', 2 => '待开始', 3 => '已拒绝']);

            $form->display('created_at');
            $form->display('updated_at');
        });
    }
}
