<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\Grid\RefuseCourse;
use App\Admin\Actions\Grid\VerifyCourse;
use App\Admin\Repositories\Course;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;
use Illuminate\Http\Request;

class TeacherCourseController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new Course(['organization','adder','province_info','city_info','district_info']), function (Grid $grid) {
            $grid->model()->where('role',3)->whereNotIn('adder_role',[0]);
            $grid->column('number','编号');
            $grid->column('adder_name','发布者');
            $grid->column('name','标题');
            $grid->column('status','状态')->using([0 => '待审核', 1 => '已通过',2 => '已结束',3 => '已拒绝']);
            $grid->column('reason','拒绝原因');
            $grid->column('method','授课方式');
            $grid->column('class_commission','课时佣金');
            $grid->column('address','上课地点');
            $grid->column('contact','联系人');
            $grid->column('mobile','联系手机');
            $grid->column('end_time','失效时间');
            $grid->column('is_recommend','是否推荐')->select([0 => '否', 1 => '是']);
            /*$grid->column('type','辅导类型');
            $grid->column('subject','科目');
            $grid->column('grade','年级');*/

            $grid->column('is_on','是否上架')->select([0 => '否', 1 => '是']);
            $grid->column('visit_count','浏览人数');
            $grid->column('buyer_count','联系人数');


            /*$grid->column('region','省市区')->display(function () {
                return $this->province_info->region_name.$this->city_info->region_name.$this->district_info->region_name;
            });
            $grid->column('adder.name','创建人');
            $grid->column('created_at','创建时间');*/
        
            $grid->filter(function (Grid\Filter $filter) {
                $filter->like('number','编号');
                $filter->like('name','标题');
                $filter->equal('status','状态')->select([0 => '待审核', 1 => '已通过',3 => '已拒绝']);
                $filter->equal('method','授课方式')->multipleSelect([0 => '待审核', 1 => '已通过',3 => '已拒绝']);
                $filter->equal('province','省份')->select('/api/city')->load('city','/api/city');
                $filter->equal('city','城市')->select('/api/city')->load('district_id','/api/city');
                $filter->equal('district','区县')->select('/api/city');
                $filter->equal('course_status','是否失效')->radio([0 => '否', 1 => '是']);
                $filter->equal('is_recommend','是否推荐')->radio([0 => '否', 1 => '是']);
                $filter->equal('is_on','是否上架')->radio([0 => '否', 1 => '是']);
                $filter->like('adder_name','发布者');
                $filter->whereBetween('created_at', function ($q) {
                    $start = $this->input['start'] ?? null;
                    $end = $this->input['end'] ?? null;

                    if ($start !== null) {
                        $q->where('created_at', '>=', $start);
                    }

                    if ($end !== null) {
                        $q->where('created_at', '<=', $end);
                    }
                },'发布时间')->datetime();
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
            $show->field('number','编号');
            $show->field('adder_name','发布者');
            $show->field('name','标题');
            $show->field('status','状态')->using([0 => '待审核', 1 => '已通过',2 => '已结束',3 => '已拒绝']);
            $show->field('method','授课方式');
            $show->field('class_commission','课时佣金');
            $show->field('address','上课地点');
            $show->map('longitude','latitude');
            $show->field('contact','联系人');
            $show->field('mobile','联系手机');
            $show->field('created_at','发布时间');
            $show->field('end_time','失效时间');
            $show->field('is_recommend','是否推荐')->select([0 => '否', 1 => '是']);
            $show->field('is_on','是否上架')->select([0 => '否', 1 => '是']);
            $show->field('visit_count','浏览人数');
            $show->field('buyer_count','联系人数');
            $show->field('reviewer','审核人');
            $show->field('update_at','审核时间');
            $show->field('reason','拒绝原因');
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
            $form->text('organ_id');
            $form->text('name');
            $form->text('type');
            $form->text('method');
            $form->text('subject');
            $form->text('count');
            $form->text('class_price');
            $form->text('duration');
            $form->text('class_duration');
            $form->text('base_count');
            $form->text('base_price');
            $form->text('improve_price');
            $form->text('max_price');
            $form->text('introduction');
            $form->text('adder_id');
            $form->select('status')->options([0 => '待审核',1 => '已通过',3 => '已拒绝']);
            $form->select('is_on','是否上架')->options([0 => '否',1 => '是']);
            $form->text('reviewer_id');
            $form->text('reason');
            $form->radio('is_recommend','是否推荐')->options([0 => '否', 1 => '是']);
        
            $form->display('created_at');
            $form->display('updated_at');
        });
    }
}
