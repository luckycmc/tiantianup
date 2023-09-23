<?php

namespace App\Admin\Controllers;

use App\Admin\Repositories\TeacherRealAuth;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class TeacherRealAuthController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new TeacherRealAuth(), function (Grid $grid) {
            $grid->column('id')->sortable();
            $grid->column('user_id');
            $grid->column('id_card_front');
            $grid->column('id_card_backend');
            $grid->column('real_name');
            $grid->column('id_card_no');
            $grid->column('status');
            $grid->column('reason');
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
        return Show::make($id, new TeacherRealAuth(), function (Show $show) {
            $show->field('id');
            $show->field('user_id');
            $show->field('id_card_front');
            $show->field('id_card_backend');
            $show->field('real_name');
            $show->field('id_card_no');
            $show->field('status');
            $show->field('reason');
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
        return Form::make(new TeacherRealAuth(), function (Form $form) {
            $form->display('id');
            $form->text('user_id');
            $form->text('id_card_front');
            $form->text('id_card_backend');
            $form->text('real_name');
            $form->text('id_card_no');
            $form->text('status');
            $form->text('reason');
        
            $form->display('created_at');
            $form->display('updated_at');
        });
    }
}
