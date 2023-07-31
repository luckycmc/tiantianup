<?php

namespace App\Admin\Controllers;

use App\Admin\Repositories\Organization;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class OrganizationController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new Organization(), function (Grid $grid) {
            $grid->column('id')->sortable();
            $grid->column('name');
            $grid->column('type');
            $grid->column('nature');
            $grid->column('contact');
            $grid->column('mobile');
            $grid->column('id_card_no');
            $grid->column('province_id');
            $grid->column('city_id');
            $grid->column('district_id');
            $grid->column('address');
            $grid->column('door_image');
            $grid->column('business_license');
            $grid->column('status');
            $grid->column('reviewer_id');
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
        return Show::make($id, new Organization(), function (Show $show) {
            $show->field('id');
            $show->field('name');
            $show->field('type');
            $show->field('nature');
            $show->field('contact');
            $show->field('mobile');
            $show->field('id_card_no');
            $show->field('province_id');
            $show->field('city_id');
            $show->field('district_id');
            $show->field('address');
            $show->field('door_image');
            $show->field('business_license');
            $show->field('status');
            $show->field('reviewer_id');
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
        return Form::make(new Organization(), function (Form $form) {
            $form->display('id');
            $form->text('name');
            $form->text('type');
            $form->text('nature');
            $form->text('contact');
            $form->text('mobile');
            $form->text('id_card_no');
            $form->text('province_id');
            $form->text('city_id');
            $form->text('district_id');
            $form->text('address');
            $form->text('door_image');
            $form->text('business_license');
            $form->text('status');
            $form->text('reviewer_id');
        
            $form->display('created_at');
            $form->display('updated_at');
        });
    }
}
