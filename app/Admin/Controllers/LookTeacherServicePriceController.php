<?php

namespace App\Admin\Controllers;

use App\Admin\Repositories\ServicePrice;
use App\Models\Region;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class LookTeacherServicePriceController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new ServicePrice(), function (Grid $grid) {
            $grid->model()->where('type',2);
            $grid->column('id')->sortable();
            $grid->column('price','服务费');
            $grid->column('start_time','开始时间');
            $grid->column('end_time','结束时间');
            $grid->column('region','地区')->display(function () {
                return $this->province.$this->city.$this->district;
            });
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
            $form->text('price','服务费');
            $form->hidden('type')->default(2);
            $form->dateRange('start_time','end_time','有效期');
            $form->select('province','省')->options('/api/city')->load('city','/api/city')->saving(function ($value) {
                return Region::where('id',$value)->value('region_name');
            });
            $form->select('city','市')->options('/api/city')->load('district','/api/city')->saving(function ($value) {
                return Region::where('id',$value)->value('region_name');
            });
            $form->select('district','区')->options('/api/city')->saving(function ($value) {
                return Region::where('id',$value)->value('region_name');
            });
            $form->text('adder','添加人');
        
            $form->display('created_at');
            $form->display('updated_at');
        });
    }
}
