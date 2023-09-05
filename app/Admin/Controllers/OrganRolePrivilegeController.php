<?php

namespace App\Admin\Controllers;

use App\Admin\Repositories\OrganRolePrivilege;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class OrganRolePrivilegeController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new OrganRolePrivilege(), function (Grid $grid) {
            $grid->column('id')->sortable();
            $grid->column('role_id');
            $grid->column('privilege_id');
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
        return Show::make($id, new OrganRolePrivilege(), function (Show $show) {
            $show->field('id');
            $show->field('role_id');
            $show->field('privilege_id');
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
        return Form::make(new OrganRolePrivilege(), function (Form $form) {
            $form->display('id');
            $form->text('role_id');
            $form->text('privilege_id');
        
            $form->display('created_at');
            $form->display('updated_at');
        });
    }
}
