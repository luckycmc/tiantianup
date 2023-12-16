<?php

namespace App\Admin\Controllers;

use App\Admin\Repositories\Notice;
use Dcat\Admin\Admin;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class NoticeController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new Notice(), function (Grid $grid) {
            $grid->column('id')->sortable();
            $grid->column('title');
            $grid->column('content');
            $grid->column('status');
            $grid->column('object');
            $grid->column('author');
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
        return Show::make($id, new Notice(), function (Show $show) {
            $show->field('id');
            $show->field('title');
            $show->field('content');
            $show->field('status');
            $show->field('object');
            $show->field('author');
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
        return Form::make(new Notice(), function (Form $form) {
            $form->display('id');
            $form->text('title');
            $form->editor('content');
            $form->checkbox('object','对象')->options(['1' => '学生', '2' => '家长', '3' => '教师', '4' => '机构'])->saving(function ($value) {
                return implode(',', $value);
            });
            $form->hidden('author')->value(Admin::user()->name);
        
            $form->display('created_at');
            $form->display('updated_at');
        });
    }
}
