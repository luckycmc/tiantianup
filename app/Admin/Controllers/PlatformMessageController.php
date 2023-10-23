<?php

namespace App\Admin\Controllers;

use App\Admin\Repositories\PlatformMessage;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class PlatformMessageController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new PlatformMessage(), function (Grid $grid) {
            $grid->column('id')->sortable();
            $grid->column('name');
            $grid->column('content');
            $grid->column('send_platform');
            $grid->column('status')->using([0 => '未读', 1 => '已读']);
            $grid->column('created_at');
            $grid->column('updated_at')->sortable();
        
            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('id');
        
            });
            $grid->disableEditButton();
            $grid->disableCreateButton();
            $grid->disableDeleteButton();
            $grid->export();
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
        return Show::make($id, new PlatformMessage(), function (Show $show) {
            $show->field('id');
            $show->field('name');
            $show->field('content');
            $show->field('send_platform');
            $show->field('status')->as([0 => '未读', 1 => '已读']);
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
        return Form::make(new PlatformMessage(), function (Form $form) {
            $form->display('id');
            $form->text('name');
            $form->text('content');
            $form->text('send_platform');
            $form->text('status');
        
            $form->display('created_at');
            $form->display('updated_at');
        });
    }
}
