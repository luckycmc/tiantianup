<?php

namespace App\Admin\Controllers;

use App\Admin\Repositories\ServicePrice;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class TeacherDealServicePriceController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new ServicePrice(), function (Grid $grid) {
            $grid->model()->where('type',1);
            $grid->column('id')->sortable();
            $grid->column('price');
            $grid->column('type');
            $grid->column('start_time');
            $grid->column('end_time');
            $grid->column('province');
            $grid->column('city');
            $grid->column('district');
            $grid->column('adder');
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
        return Show::make($id, new ServicePrice(), function (Show $show) {
            $show->field('id');
            $show->field('price');
            $show->field('type');
            $show->field('start_time');
            $show->field('end_time');
            $show->field('province');
            $show->field('city');
            $show->field('district');
            $show->field('adder');
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
        return Form::make(new ServicePrice(), function (Form $form) {
            $form->display('id');
            $form->text('price');
            $form->text('type');
            $form->text('start_time');
            $form->text('end_time');
            $form->text('province');
            $form->text('city');
            $form->text('district');
            $form->text('adder');
        
            $form->display('created_at');
            $form->display('updated_at');
        });
    }
}
