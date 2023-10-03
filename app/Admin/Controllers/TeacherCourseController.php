<?php

namespace App\Admin\Controllers;

use App\Admin\Repositories\Course;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;
use Illuminate\Http\Request;

class TeacherCourseController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new Course(), function (Grid $grid) {
            $grid->model()->where('role',3);
            $grid->column('id')->sortable();
            $grid->column('organ_id');
            $grid->column('name');
            $grid->column('type');
            $grid->column('method');
            $grid->column('subject');
            $grid->column('count');
            $grid->column('class_price');
            $grid->column('duration');
            $grid->column('class_duration');
            $grid->column('base_count');
            $grid->column('base_price');
            $grid->column('improve_price');
            $grid->column('max_price');
            $grid->column('introduction');
            $grid->column('adder_id');
            $grid->column('status');
            $grid->column('reviewer_id');
            $grid->column('reason');
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
