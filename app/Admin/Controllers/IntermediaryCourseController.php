<?php

namespace App\Admin\Controllers;

use App\Admin\Repositories\Course;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;
use Illuminate\Http\Request;

class IntermediaryCourseController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new Course('adder'), function (Grid $grid) {
            $grid->model()->where('adder_role',0);
            $grid->column('number','编号');
            $grid->column('created_at','发布时间');
            $grid->column('end_time','失效时间');
            $grid->column('subject','科目');
            $grid->column('gender','学员性别');
            $grid->column('grade','年级');
            $grid->column('region','省市区')->display(function () {
                return $this->province.$this->city.$this->district;
            });
            $grid->column('address','上课地点');
            $grid->column('class_price','费用');
            $grid->column('class_duration','上课时长');
            $grid->column('mobile','联系方式');
            $grid->column('adder.name','发布人');
            $grid->column('buyer_count','付费人数');
            $grid->column('visitor_count','浏览人数');
        
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
        return Show::make($id, new Course(), function (Show $show) {
            $show->field('id');
            $show->field('organ_id');
            $show->field('name');
            $show->field('type');
            $show->field('method');
            $show->field('subject');
            $show->field('count');
            $show->field('class_price');
            $show->field('duration');
            $show->field('class_duration');
            $show->field('base_count');
            $show->field('base_price');
            $show->field('improve_price');
            $show->field('max_price');
            $show->field('introduction');
            $show->field('adder_id');
            $show->field('status');
            $show->field('reviewer_id');
            $show->field('reason');
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
        return Form::make(new Course(), function (Form $form) {
            $form->display('id');
            $form->text('organ_id');
            $form->text('name');
            $form->text('type');
            $form->text('method');
            $form->text('subject');
            $form->text('count');
            $form->text('class_price');
            $form->text('duration');
            $form->text('class_duration');
            $form->text('base_count');
            $form->text('base_price');
            $form->text('improve_price');
            $form->text('max_price');
            $form->text('introduction');
            $form->text('adder_id');
            $form->text('status');
            $form->text('reviewer_id');
            $form->text('reason');
        
            $form->display('created_at');
            $form->display('updated_at');
        });
    }
}
