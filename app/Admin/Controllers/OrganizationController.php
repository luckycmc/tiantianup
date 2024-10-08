<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\Grid\DisableOrgan;
use App\Admin\Actions\Grid\EnableOrgan;
use App\Admin\Actions\Grid\RefuseOrgan;
use App\Admin\Actions\Grid\VerifyOrgan;
use App\Admin\Repositories\Organization;
use Dcat\Admin\Admin;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Layout\Content;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;
use Dcat\Admin\Widgets\Tab;

class OrganizationController extends AdminController
{
    public function index(Content $content)
    {
        $tab = Tab::make();
        $tab->add('全部',$this->grid(),true);
        $tab->add('待审核',$this->grid0());
        $tab->add('已通过',$this->grid1());
        $tab->add('已拒绝',$this->grid2());
        return $content->body($tab->withCard());
    }
    
    
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
            $grid->column('training_type','培训类型');
            $grid->column('nature','机构性质');
            $grid->column('mobile');
            $grid->column('id_card_no');
            $grid->column('region','省市区')->display(function () {
                return $this->province->region_name.$this->city->region_name.$this->district->region_name;
            });
            $grid->column('address','详细地址');
            $grid->column('contact');
            $grid->column('status')->using([0 => '待审核', 1 => '已通过', 2 => '已拒绝']);
            $grid->column('user.status','账户状态')->using([1 => '正常', 2 => '禁用']);
            $grid->column('reviewer.name','审核人');
            $grid->column('updated_at','审核时间');
        
            $grid->filter(function (Grid\Filter $filter) {
                $filter->like('name');
                $filter->like('user.number','ID');
                $filter->equal('type')->select('/api/organ_type');
                $filter->equal('nature','机构性质')->select('/api/nature');
            });
            // 禁用删除
            $grid->disableDeleteButton();
            // 导出
            $grid->export()->rows(function ($rows) {
                foreach ($rows as &$row) {
                    $status_arr = ['待审核','已通过','已拒绝'];
                    $row['status'] = $status_arr[$row['status']];
                    $row['user']['status'] = $row['user']['status'] == 1 ? '正常' : '禁用';
                    $row['region'] = isset($row->province) ? $row->province->region_name.$row->city->region_name.$row->district->region_name : null;
                }
                return $rows;
            });
            $grid->actions(function ($actions) {
                $status = $actions->row->user->status;
                if (!in_array($status,[2,3])) {
                    $actions->append(new DisableOrgan());
                } else {
                    $actions->append(new EnableOrgan());
                }
            });

        });
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid0()
    {
        return Grid::make(new Organization(['user','province','city','district','reviewer']), function (Grid $grid) {
            $grid->model()->where('status',0);
            $grid->column('user.number','ID');
            $grid->column('name');
            $grid->column('type');
            $grid->column('training_type','培训类型');
            $grid->column('nature','机构性质');
            $grid->column('contact','负责人');
            $grid->column('mobile');
            $grid->column('id_card_no');
            $grid->column('region','省市区')->display(function () {
                return $this->province->region_name.$this->city->region_name.$this->district->region_name;
            });
            $grid->column('status')->using([0 => '待审核', 1 => '已通过', 2 => '已拒绝']);
            $grid->column('user.status','状态')->select([1 => '正常', 0 => '禁用']);
            $grid->column('reviewer.name','审核人');
            $grid->column('updated_at','审核时间');

            $grid->filter(function (Grid\Filter $filter) {
                $filter->like('name');
                $filter->like('user.number','ID');
                $filter->equal('type')->select('/api/organ_type');
                $filter->equal('nature','培训类型')->select('/api/nature');
            });
            // 禁用删除
            $grid->disableDeleteButton();
            // 导出
            $grid->export();
            $grid->actions(function ($actions) {
                $status = $actions->row->status;
                if ($status == 0) {
                    $actions->append(new VerifyOrgan());
                    $actions->append(new RefuseOrgan());
                }
            });
        });
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid1()
    {
        return Grid::make(new Organization(['user','province','city','district','reviewer']), function (Grid $grid) {
            $grid->model()->where('status',1);
            $grid->column('user.number','ID');
            $grid->column('name');
            $grid->column('type');
            $grid->column('training_type','培训类型');
            $grid->column('nature','机构性质');
            $grid->column('user.name','负责人');
            $grid->column('mobile');
            $grid->column('id_card_no');
            $grid->column('region','省市区')->display(function () {
                return $this->province->region_name.$this->city->region_name.$this->district->region_name;
            });
            $grid->column('contact');
            $grid->column('status')->using([0 => '待审核', 1 => '已通过', 2 => '已拒绝']);
            $grid->column('user.status','账户状态')->select([1 => '正常', 0 => '禁用']);
            $grid->column('reviewer.name','审核人');
            $grid->column('updated_at','审核时间');

            $grid->filter(function (Grid\Filter $filter) {
                $filter->like('name');
                $filter->like('user.number','ID');
                $filter->equal('type')->select('/api/organ_type');
                $filter->equal('nature','培训类型')->select('/api/nature');
            });
            // 禁用删除
            $grid->disableDeleteButton();
            // 导出
            $grid->export();
        });
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid2()
    {
        return Grid::make(new Organization(['user','province','city','district','reviewer']), function (Grid $grid) {
            $grid->model()->where('status',2);
            $grid->column('user.number','ID');
            $grid->column('name');
            $grid->column('type');
            $grid->column('training_type','培训类型');
            $grid->column('nature','机构性质');
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
                $filter->like('user.number','ID');
                $filter->equal('type')->select('/api/organ_type');
                $filter->equal('nature','培训类型')->select('/api/nature');
            });
            // 禁用删除
            $grid->disableDeleteButton();
            // 导出
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
            if ($form->action() == 'update') {
                $form->display('user.number','ID');
            }
            $form->text('name');
            $form->select('type')->options('/api/organ_type');
            $form->select('training_type','培训类型')->options('/api/training_type');
            $form->select('nature')->options('/api/nature');
            $form->text('contact','负责人');
            $form->text('mobile');
            $form->text('id_card_no');
            $form->select('province_id','省份')->options('/api/operational_city')->load('city_id', '/api/operational_city');
            $form->select('city_id','城市')->options('/api/operational_city')->load('district_id', '/api/operational_city');
            $form->select('district_id','区县');
            $form->text('address');
            $form->image('door_image','机构logo')->uniqueName()->move('public/organization/images')->saveFullUrl();
            $form->image('business_license')->move('public/organization/images')->saveFullUrl();
            if ($form->action() == 'update') {
                $form->hidden('user.status','账号状态');
            }
            if ($form->action() == 'update') {
                $form->select('status')->options([0 => '待审核', 1 => '已通过', 2 => '已拒绝']);
                $form->select('reviewer_id','审核人')->options('/api/admin_users');
            }
            $form->hidden('status')->default(1);
            $form->multipleImage('images','机构场所照片')->saveFullUrl();
            $form->display('created_at');
            $form->display('updated_at');
        });
    }
}
