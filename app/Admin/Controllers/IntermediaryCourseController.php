<?php

namespace App\Admin\Controllers;

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
        return Grid::make(new Course('adder'), function (Grid $grid) {
            $grid->model()->where('adder_role',0)->orderByDesc('created_at');
            $grid->column('number','编号');
            $grid->column('created_at','发布时间');
            $grid->column('end_time','失效时间');
            $grid->column('subject','科目');
            $grid->column('gender','学员性别')->using([0 => '女', 1 => '男']);
            $grid->column('grade','年级');
            $grid->column('region','省市区')->display(function () {
                // dd($this->province,$this->city,$this->district);
                $province = Region::where('id',$this->province)->value('region_name');
                $city = Region::where('id',$this->city)->value('region_name');
                $district = Region::where('id',$this->district)->value('region_name');
                return $province.$city.$district;
            });
            $grid->column('address','上课地点');
            $grid->column('class_price','费用(元)');
            $grid->column('class_duration','上课时长(分钟)');
            $grid->column('platform_class_date','上课时间');
            $grid->column('mobile','联系方式');
            $grid->column('adder_name','发布人');
            $grid->column('buyer_count','付费人数');
            $grid->column('visit_count','浏览人数');
        
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
            $grid->export()->rows(function ($rows) {
                foreach ($rows as &$row) {
                    $arr = ['待审核','已通过','已关闭','已拒绝'];
                    $row['status'] = $arr[$row['status']];
                    $row['region'] = $this->province_info->region_name.$this->city_info->region_name.$this->district_info->region_name;
                    $row['is_recommend'] = $row['is_recommend'] == 0 ? '否' : '是';
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
            $form->text('platform_class_date','上课时间');
            $form->select('province','省')->options('/api/city')->load('city','/api/city')->required();
            $form->select('city','市')->options('/api/city')->load('district','/api/city')->required();
            $form->select('district','区')->options('/api/city')->required();
            $form->text('address','上课地点')->required();
            $form->number('class_duration','上课时长(分钟)');
            $form->number('class_price','费用(元)');
            $form->text('requirement','要求');
            $form->text('detail','详情');
            $form->number('valid_time','有效期(天)');
            $form->text('qq_account','QQ号');
            $form->text('wechat_account','微信号');
            $form->mobile('mobile','手机号');
            $form->text('contact','联系人');
            $form->hidden('adder_role')->default(0);
            $form->hidden('role')->default(3);
            $form->hidden('adder_name')->default(Admin::user()->name);
            $form->hidden('class_date');
            $form->hidden('end_time');
            $form->saving(function (Form $form) {
                $form->deleteInput('class_date_start');
                $form->deleteInput('class_date_end');
                $form->end_time = Carbon::now()->addDays($form->valid_time);
            });
            $form->saved(function (Form $form, $result) {
                // 判断是否是新增操作
                if ($form->isCreating()) {
                    // 也可以这样获取自增ID
                    $course_id = $form->getKey();

                    if (!$course_id) {
                        return $form->error('数据保存失败');
                    }
                    $number = create_df_number($course_id);
                    $form->model()->update(['number' => $number]);
                }
                $form->model()->update(['status' => 0]);
            });

            $form->display('created_at');
            $form->display('updated_at');
        });
    }
}
