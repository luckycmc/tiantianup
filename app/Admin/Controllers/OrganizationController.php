<?php

namespace App\Admin\Controllers;

use App\Admin\Repositories\Organization;
use Dcat\Admin\Admin;
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
        return Grid::make(new Organization(['user','province','city','district','reviewer']), function (Grid $grid) {
            $grid->column('user.number','ID');
            $grid->column('name');
            $grid->column('type');
            $grid->column('nature','培训类型');
            $grid->column('user.name','负责人');
            $grid->column('mobile');
            $grid->column('id_card_no');
            $grid->column('region','省市区')->display(function () {
                return $this->province->region_name.$this->city->region_name.$this->district->region_name;
            });
            $grid->column('contact');
            $grid->column('status')->using([0 => '待审核', 1 => '已通过', 2 => '已拒绝']);
            $grid->column('user.status')->select([1 => '正常', 0 => '禁用']);
            $grid->column('reviewer.name','审核人');
            $grid->column('updated_at','审核时间');
        
            $grid->filter(function (Grid\Filter $filter) {
                $filter->like('name');
                $filter->equal('type')->select('/api/organ_type');
                $filter->equal('nature','培训类型')->select('/api/nature');
            });
            // 禁用删除
            $grid->disableDeleteButton();
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
        return Show::make($id, new Organization(['user','province','city','district','reviewer']), function (Show $show) {
            $show->field('user.number','ID');
            $show->field('name');
            $show->field('type');
            $show->field('nature','培训类型');
            $show->field('user.name','负责人');
            $show->field('mobile');
            $show->field('id_card_no');
            $show->field('region','省市区')->as(function () {
                return $this->province->region_name.$this->city->region_name.$this->district->region_name;
            });
            $show->field('status')->using([0 => '待审核', 1 => '已通过', 2 => '已拒绝']);
            $show->field('reviewer.name','审核人');
            $show->field('updated_at','审核时间');
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Form::make(new Organization(['user','province','city','district','reviewer']), function (Form $form) {
            $form->display('user.number','ID');
            $form->text('name');
            $form->text('type');
            $form->text('nature');
            $form->text('contact');
            $form->text('mobile');
            $form->text('id_card_no');
            $form->select('province_id','省份')->options('/api/city')->load('city_id', '/api/city');
            $form->select('city_id','城市')->options('/api/city')->load('district_id', '/api/city');
            $form->select('district_id','区县');
            $form->text('address');
            $form->text('door_image');
            $form->text('business_license');
            $form->hidden('user.status','账号状态');
            $form->select('status')->options([0 => '待审核', 1 => '已通过', 2 => '已拒绝']);
            $form->select('reviewer_id','审核人')->options('/api/admin_users');

            $form->display('created_at');
            $form->display('updated_at');
        });
    }
}
