<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\Grid\RefuseCourse;
use App\Admin\Actions\Grid\VerifyCourse;
use App\Admin\Repositories\Course;
use App\Models\Region;
use Carbon\Carbon;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;
use Illuminate\Http\Request;

class IntermediaryCourseController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new Course('adder'), function (Grid $grid) {
            $grid->model()->where('adder_role',0);
            $grid->column('number','编号');
            $grid->column('created_at','发布时间');
            $grid->column('end_time','失效时间');
            $grid->column('subject','科目');
            $grid->column('gender','学员性别');
            $grid->column('grade','年级');
            $grid->column('region','省市区')->display(function () {
                return $this->province.$this->city.$this->district;
            });
            $grid->column('address','上课地点');
            $grid->column('class_price','费用');
            $grid->column('class_duration','上课时长');
            $grid->column('mobile','联系方式');
            $grid->column('adder.name','发布人');
            $grid->column('buyer_count','付费人数');
            $grid->column('visitor_count','浏览人数');
        
            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('id');
        
            });
            $grid->actions(function ($actions) {
                $status = $actions->row->status;
                if ($status == 0) {
                    $actions->append(new VerifyCourse());
                    $actions->append(new RefuseCourse());
                }
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
        return Show::make($id, new Course(), function (Show $show) {
            $show->field('id');
            $show->field('organ_id');
            $show->field('name');
            $show->field('type');
            $show->field('method');
            $show->field('subject');
            $show->field('count');
            $show->field('class_price');
            $show->field('duration');
            $show->field('class_duration');
            $show->field('base_count');
            $show->field('base_price');
            $show->field('improve_price');
            $show->field('max_price');
            $show->field('introduction');
            $show->field('adder_id');
            $show->field('status');
            $show->field('reviewer_id');
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
        return Form::make(new Course(), function (Form $form) {
            $form->display('id');
            $form->text('subject','科目');
            $form->text('grade','年级');
            $form->radio('gender','性别')->options([0 => '女',1 => '男']);
            $form->datetimeRange('class_date_start','class_date_end','上课时间');
            $form->select('province','省')->options('/api/city')->load('city','/api/city');
            $form->select('city','市')->options('/api/city')->load('district','/api/city');
            $form->select('district','区')->options('/api/city');
            $form->text('address','上课地点');
            $form->number('class_duration','上课时长');
            $form->number('class_price','费用');
            $form->text('requirement','要求');
            $form->text('detail','详情');
            $form->number('valid_time','有效期');
            $form->text('qq_account','QQ号');
            $form->text('wechat_account','微信号');
            $form->mobile('mobile','手机号');
            $form->text('contact','联系人');
            $form->hidden('adder_role')->default(0);
            $form->hidden('role')->default(3);
            $form->saving(function (Form $form) {
                dd($form->class_date_start,$form->class_date_end);
                $form->class_date = json_encode([$form->class_date_start,$form->class_date_end]);
                $form->deleteInput('class_date_start');
                $form->deleteInput('class_date_end');
                $form->end_time = Carbon::now()->addDays($form->valid_time);
                $form->province = Region::where('id', $form->province)->value('region_name');
                $form->city = Region::where('id', $form->city)->value('region_name');
                $form->district = Region::where('id', $form->district)->value('region_name');
            });

            $form->display('created_at');
            $form->display('updated_at');
        });
    }
}
