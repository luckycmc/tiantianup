<?php

namespace App\Admin\Controllers;

use App\Admin\Repositories\Banner;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class BannerController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new Banner(), function (Grid $grid) {
            $grid->column('id')->sortable();
            $grid->column('url')->image('',60,60);
            $grid->column('object');
            $grid->column('link');
            $grid->column('adder');
            $grid->column('editor');
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
        return Show::make($id, new Banner(), function (Show $show) {
            $show->field('id');
            $show->field('url');
            $show->field('object');
            $show->field('link');
            $show->field('adder');
            $show->field('editor');
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
        return Form::make(new Banner(), function (Form $form) {
            $form->display('id');
            $form->image('url')->uniqueName()->saveFullUrl()->saving(function ($url) {
                $arr = explode('?',$url);
                return $arr[0];
            });
            $form->checkbox('object')->options(['学生' => '学生', '家长' => '家长', '教师' => '教师', '机构' => '机构'])->canCheckAll()->saving(function ($value) {
                return implode(',',$value);
            });
            $form->text('link');
            $form->text('adder');
            $form->text('editor');
        
            $form->display('created_at');
            $form->display('updated_at');
        });
    }
}
