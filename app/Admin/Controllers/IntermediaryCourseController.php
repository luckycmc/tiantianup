<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\Grid\CourseDown;
use App\Admin\Actions\Grid\CourseUp;
use App\Admin\Actions\Grid\RefuseCourse;
use App\Admin\Actions\Grid\VerifyCourse;
use App\Admin\Repositories\Course;
use App\Models\Region;
use Carbon\Carbon;
use Dcat\Admin\Admin;
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
        return Grid::make(new Course(['province_info','city_info','district_info']), function (Grid $grid) {
            $grid->model()->where('adder_role',0)->orderByDesc('created_at');
            $grid->column('number','编号');
            $grid->column('method','授课方式');
            $grid->column('created_at','发布时间');
            $grid->column('end_time','失效时间');
            $grid->column('name','标题');
            $grid->column('region','所在城市')->display(function () {
                $province = Region::where('id',$this->province)->value('region_name');
                $city = Region::where('id',$this->city)->value('region_name');
                return $province.$city;
            });
            $grid->column('status')->using([0 => '待审核', 1 => '已通过', 3 => '已拒绝']);
            $grid->column('is_on','是否上架')->using([0 => '否', 1 => '是']);
            $grid->column('reason','拒绝原因');
            $grid->column('adder_name','发布人');
            $grid->column('entry_number','付费人数');
            $grid->column('visit_count','浏览人数');
        
            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('number','需求编号');
                $filter->like('name','标题');
                $filter->like('adder_name','发布人 ');
                $filter->equal('status')->select([0 => '待审核', 1 => '已通过', 3 => '已拒绝']);
                $filter->equal('is_on','是否上架')->select([0 => '否', 1 => '是']);
                $filter->equal('is_invalid','是否失效')->radio([1 => '是',0 => '否']);
                $filter->equal('method','授课方式')->radio(['线下' => '线下','线上' => '线上','线下/线上' => '线下/线上']);
                $filter->whereBetween('created_at', function ($q) {
                    $start = $this->input['start'] ?? null;
                    $end = $this->input['end'] ?? null;

                    if ($start !== null) {
                        $q->where('created_at', '>=', $start);
                    }

                    if ($end !== null) {
                        $q->where('created_at', '<=', $end);
                    }
                })->datetime();
                $filter->equal('province_id','省份')->select('/api/city')->load('city_id','/api/city');
                $filter->equal('city_id','城市')->select('/api/city')->load('district_id','/api/city');
            });
            $grid->actions(function ($actions) {
                $status = $actions->row->status;
                if ($status == 0) {
                    $actions->append(new VerifyCourse());
                    $actions->append(new RefuseCourse());
                }
                $is_on = $actions->row->is_on;
                if ($is_on == 1) {
                    $actions->append(new CourseDown());
                } else {
                    $actions->append(new CourseUp());
                }
            });
            $grid->export()->rows(function ($rows) {
                foreach ($rows as &$row) {
                    $arr = ['待审核','已通过','已关闭','已拒绝'];
                    $row['status'] = $arr[$row['status']];
                    $row['region'] = $row->province_info->region_name.$row->city_info->region_name;
                }
                return $rows;
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
            $show->field('name','标题');
            $show->field('method');
            $show->field('city','所在城市')->as(function () {
                $province = Region::where('id',$this->province)->value('region_name');
                $city = Region::where('id',$this->city)->value('region_name');
                return $province.$city;
            });

            $show->contents('详情')->as(function () {
                return html_entity_decode("{aa<br />bb}");
            });
            $show->field('valid_time','有效期');
            $show->field('end_time','失效时间');
            $show->field('contact','联系人');
            $show->field('qq_account','QQ号');
            $show->field('wechat_account','微信号');
            $show->field('mobile','手机号');
            $show->field('reviewer','审核员');
            $show->field('created_at','发布时间');
            $show->field('updated_at','审核时间');
            if ($show->model()->status == 2) {
                $show->field('reason');
            }
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
            $form->text('name','标题');
            $form->select('method')->options(['线下' => '线下','线上' => '线上','线下/线上' => '线下/线上'])->when('!=','线上',function (Form $form) {
                $form->select('province','省')->options('/api/city')->load('city','/api/city');
                $form->select('city','市')->options('/api/city');
            });
            $form->editor('introduction','详情');
            $form->number('valid_time','有效期(天)');
            $form->text('contact','联系人');
            $form->text('qq_account','QQ号')->rules('required_without_all:wechat_account,mobile',[
                'required_without_all' => 'QQ号、微信号、手机号至少填写一项'
            ]);
            $form->text('wechat_account','微信号')->rules('required_without_all:qq_account,mobile',[
                'required_without_all' => 'QQ号、微信号、手机号至少填写一项'
            ]);
            $form->mobile('mobile','手机号')->rules('required_without_all:wechat_account,qq_account',[
                'required_without_all' => 'QQ号、微信号、手机号至少填写一项'
            ]);
            $form->hidden('adder_role')->default(0);
            $form->hidden('role')->default(3);
            $form->hidden('is_on')->default(0);
            $form->hidden('status');
            $form->saving(function (Form $form) {
                $form->status = 0;
                if ($form->method == '线上') {
                    $form->province = null;
                    $form->city = null;
                }
            });
            $form->hidden('adder_name')->default(Admin::user()->name);
            $form->hidden('class_date');
            $form->display('created_at');
            $form->display('updated_at');
        });
    }
}
