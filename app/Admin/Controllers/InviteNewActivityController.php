<?php

namespace App\Admin\Controllers;

use App\Admin\Repositories\Activity;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class InviteNewActivityController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new Activity(), function (Grid $grid) {
            $grid->model()->where('type',1);
            $grid->column('id','活动id')->sortable();
            $grid->column('name','活动名称');
            $grid->column('status','活动状态')->using([0 => '已结束',1 => '进行中']);
            $grid->column('start_time');
            $grid->column('end_time');
            $grid->column('object','活动对象');
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
            $form->text('image');
            $form->text('object');
            $form->text('type');
            $form->text('description');
            $form->text('reward');
            $form->text('introduction');
            $form->text('start_time');
            $form->text('end_time');
            $form->text('status');
        
            $form->display('created_at');
            $form->display('updated_at');
        });
    }
}
