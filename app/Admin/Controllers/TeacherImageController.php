<?php

namespace App\Admin\Controllers;

use App\Admin\Repositories\TeacherImage;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class TeacherImageController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new TeacherImage(['user']), function (Grid $grid) {
            $grid->model()->where('type',2);
            $grid->column('id')->sortable();
            $grid->column('user.name','教师名称');
            $grid->column('url')->image('',60,60);
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
        return Show::make($id, new TeacherImage(['user']), function (Show $show) {
            $show->field('id');
            $show->field('user.name','教师姓名');
            $show->field('url')->image('',60,60);
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
        return Form::make(new TeacherImage(), function (Form $form) {
            $form->display('id');
            $form->text('user_id');
            $form->image('url')->saveFullUrl();
            $form->hidden('type')->default(0);
        
            $form->display('created_at');
            $form->display('updated_at');
        });
    }
}
