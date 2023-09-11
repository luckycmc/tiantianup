<?php

namespace App\Admin\Controllers;

use App\Admin\Repositories\User;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class UserController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new User('organization'), function (Grid $grid) {
            $grid->model()->where(['role' => 4,'status' => 0]);
            $grid->column('number','ID');
            $grid->column('organization.name','机构名称');
            $grid->column('gender');
            $grid->column('mobile');
            $grid->column('birthday');
            $grid->column('age');
            $grid->column('province_id');
            $grid->column('city_id');
            $grid->column('district_id');
            $grid->column('address');
            $grid->column('school');
            $grid->column('grade');
            $grid->column('introduction');
            $grid->column('total_income');
            $grid->column('withdraw_balance');
            $grid->column('status');
            $grid->column('is_real_auth');
            $grid->column('is_education');
            $grid->column('has_teacher_cert');
            $grid->column('is_recommend');
            $grid->column('open_id');
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
        return Show::make($id, new User(), function (Show $show) {
            $show->field('id');
            $show->field('parent_id');
            $show->field('avatar');
            $show->field('nickname');
            $show->field('name');
            $show->field('number');
            $show->field('role');
            $show->field('organ_role_id');
            $show->field('gender');
            $show->field('mobile');
            $show->field('birthday');
            $show->field('age');
            $show->field('province_id');
            $show->field('city_id');
            $show->field('district_id');
            $show->field('address');
            $show->field('school');
            $show->field('grade');
            $show->field('introduction');
            $show->field('total_income');
            $show->field('withdraw_balance');
            $show->field('status');
            $show->field('is_real_auth');
            $show->field('is_education');
            $show->field('has_teacher_cert');
            $show->field('is_recommend');
            $show->field('open_id');
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
        return Form::make(new User(), function (Form $form) {
            $form->display('id');
            $form->text('parent_id');
            $form->text('avatar');
            $form->text('nickname');
            $form->text('name');
            $form->text('number');
            $form->text('role');
            $form->text('organ_role_id');
            $form->text('gender');
            $form->text('mobile');
            $form->text('birthday');
            $form->text('age');
            $form->text('province_id');
            $form->text('city_id');
            $form->text('district_id');
            $form->text('address');
            $form->text('school');
            $form->text('grade');
            $form->text('introduction');
            $form->text('total_income');
            $form->text('withdraw_balance');
            $form->text('status');
            $form->text('is_real_auth');
            $form->text('is_education');
            $form->text('has_teacher_cert');
            $form->text('is_recommend');
            $form->text('open_id');
        
            $form->display('created_at');
            $form->display('updated_at');
        });
    }
}
