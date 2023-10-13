<?php

namespace App\Admin\Controllers;

use App\Admin\Repositories\SystemMessage;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class SystemMessageController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new SystemMessage(), function (Grid $grid) {
            $grid->column('id')->sortable();
            $grid->column('name');
            $grid->column('action');
            $grid->column('site_message');
            $grid->column('text_message');
            $grid->column('official_account');
            $grid->column('object');
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
        return Show::make($id, new SystemMessage(), function (Show $show) {
            $show->field('id');
            $show->field('name');
            $show->field('action');
            $show->field('site_message');
            $show->field('text_message');
            $show->field('official_account');
            $show->field('object');
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
        return Form::make(new SystemMessage(), function (Form $form) {
            $form->display('id');
            $form->text('name');
            $form->text('action');
            $form->text('site_message');
            $form->text('text_message');
            $form->text('official_account');
            $form->text('object');
        
            $form->display('created_at');
            $form->display('updated_at');
        });
    }
}
