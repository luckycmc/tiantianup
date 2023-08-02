<?php

namespace App\Admin\Controllers;

use App\Admin\Repositories\RotateImage;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class RotateImageController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new RotateImage(), function (Grid $grid) {
            $grid->column('id')->sortable();
            $grid->column('url');
            $grid->column('show_platform');
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
        return Show::make($id, new RotateImage(), function (Show $show) {
            $show->field('id');
            $show->field('url');
            $show->field('show_platform');
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
        return Form::make(new RotateImage(), function (Form $form) {
            $form->display('id');
            $form->text('url');
            $form->text('show_platform');
        
            $form->display('created_at');
            $form->display('updated_at');
        });
    }
}
