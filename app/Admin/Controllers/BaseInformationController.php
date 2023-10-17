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
            $grid->column('user_image');
            $grid->column('teacher_image');
            $grid->column('logo');
            $grid->column('poster');
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
            $show->field('user_image');
            $show->field('teacher_image');
            $show->field('logo');
            $show->field('poster');
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
            $form->text('user_image');
            $form->text('teacher_image');
            $form->text('logo');
            $form->text('poster');
            $form->display('created_at');
            $form->display('updated_at');
        });
    }
}
