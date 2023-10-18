<?php

namespace App\Admin\Controllers;

use App\Admin\Repositories\BaseInformation;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class BaseInformationController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new BaseInformation(), function (Grid $grid) {
            $grid->column('id')->sortable();
            $grid->column('name');
            $grid->column('introduction');
            $grid->column('mobile');
            $grid->column('logo')->image('',60,60);
            $grid->column('poster')->image('',60,60);
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
        return Show::make($id, new BaseInformation(), function (Show $show) {
            $show->field('id');
            $show->field('name');
            $show->field('introduction');
            $show->field('mobile');
            $show->field('logo');
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
        return Form::make(new BaseInformation(), function (Form $form) {
            $form->display('id');
            $form->text('name');
            $form->text('introduction');
            $form->text('mobile');
            $form->image('logo')->saveFullUrl()->saving(function ($value) {
                $arr = explode('?',$value);
                return $arr[0];
            });
            $form->display('created_at');
            $form->display('updated_at');
        });
    }
}
