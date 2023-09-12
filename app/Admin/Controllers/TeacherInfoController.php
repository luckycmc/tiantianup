<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\Grid\Recommend;
use App\Admin\Repositories\TeacherInfo;
use App\Admin\Repositories\User;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class TeacherInfoController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new User(['teacher_info','province','city','district']), function (Grid $grid) {
            $grid->model()->where('role',3);
            $grid->column('number','ID');
            $grid->column('name','教师姓名');
            $grid->column('gender','性别')->using([0 => '女',1 => '男']);
            $grid->column('mobile','手机号');
            $grid->column('status','账号状态')->using([0 => '未注册', 1 => '正常', 2 => '禁用']);
            $grid->column('region','所在省市区')->display(function () {
                return $this->province->region_name.$this->city->region_name.$this->district->region_name;
            });
            $grid->column('teacher_info.highest_education','学历');
            $grid->column('is_real_auth','实名认证状态')->using([0 => '未实名', 1 => '已实名']);
            $grid->column('has_teacher_cert','是否有教师资格证')->using([0 => '否',1 => '是']);
            $grid->column('teacher_info.status','审核状态')->using([0 => '待审核', 1 => '审核通过', 2 => '拒绝']);
            $grid->column('is_recommend','推荐')->radio([0 => '否', 1 => '是']);
        
            $grid->filter(function (Grid\Filter $filter) {
                $filter->like('name');
                $filter->like('mobile','手机号码');
                $filter->equal('status','账号状态')->select([0 => '未注册', 1 => '正常', 2 => '禁用']);
                $filter->equal('teacher_info.highest_education','学历')->select('/api/education');
                $filter->equal('is_real_auth','实名认证')->select([0 => '未实名', 1 => '已实名']);
                $filter->equal('has_teacher_cert','是否有教师资格证')->radio([1 => '是', 0 => '否']);
                $filter->whereBetween('created_at',function ($q) {
                    $start = $this->input['start'] ?? null;
                    $end = $this->input['end'] ?? null;
                    $q->where('created_at', '>=', $start);
                    $q->where('created_at', '<=', $end);
                })->datetime();
                $filter->like('number','ID');
                $filter->equal('is_recommend','是否推荐')->radio([1 => '是', 0 => '否']);

            });
            $grid->disableDeleteButton();
            $grid->tools(function ($tools) {
                $tools->batch(function ($batch) {
                    $batch->add(new Recommend('批量推荐', 1));
                });
            });
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
        return Show::make($id, new User(['teacher_info','province','city','district']), function (Show $show) {
            $show->field('number','ID');
            $show->field('name','教师姓名');
            $show->field('gender','性别')->using([0 => '女',1 => '男']);
            $show->field('mobile','手机号');
            $show->field('status','账号状态')->using([0 => '未注册', 1 => '正常', 2 => '禁用']);
            $show->field('region','所在省市区')->as(function () {
                return $this->province->region_name.$this->city->region_name.$this->district->region_name;
            });
            $show->field('teacher_info.highest_education','学历');
            $show->field('is_real_auth','实名认证状态')->using([0 => '未实名', 1 => '已实名']);
            $show->field('has_teacher_cert','是否有教师资格证')->using([0 => '否',1 => '是']);
            $show->field('teacher_info.status','审核状态')->using([0 => '待审核', 1 => '审核通过', 2 => '拒绝']);
            $show->field('is_recommend','推荐')->using([0 => '否', 1 => '是']);
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Form::make(new User(['teacher_info','province','city','district']), function (Form $form) {
            $form->display('number','ID');
            $form->text('name');
            $form->text('gender');
            $form->text('mobile');
            $form->text('status');
            $form->text('region');
            $form->text('teacher_info.highest_education');
            $form->text('is_real_auth');
            $form->text('graduate_cert');
            $form->text('teacher_info.status');
            $form->text('is_recommend');
        
            $form->display('created_at');
            $form->display('updated_at');
        });
    }
}
