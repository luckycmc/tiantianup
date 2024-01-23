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

class StudentCourseController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new Course(['organization','province_info','city_info','district_info']), function (Grid $grid) {
            $grid->model()->where('role',1);
            $grid->column('number','编号');
            $grid->column('organization.name','发布机构');
            $grid->column('name','课程名称');
            $grid->column('status','状态')->using([0 => '待审核',1 => '已通过',2 => '已结束',3 => '已拒绝']);
            $grid->column('reason','拒绝原因');
            $grid->column('method','授课方式');
            $grid->column('class_price','课时费');
            $grid->column('address','上课地点');
            $grid->column('created_at','发布时间');
            $grid->column('end_time','失效时间');
            $grid->column('is_recommend','是否推荐')->select([0 => '否', 1 => '是']);
            $grid->column('is_up','是否上架')->select([0 => '否', 1 => '是']);
            $grid->column('visit_count','浏览人数');
            $grid->column('buyer_count','咨询人数');

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
                $filter->equal('is_on','是否上架')->select([0 => '否', 1 => '是']);
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
            $show->field('class_price','课时费');
            $show->field('address','上课地点');
            $show->field('contact','联系人');
            $show->field('mobile','联系手机');
            $show->field('created_at','发布时间');
            $show->field('end_time','失效时间');
            $show->field('is_recommend','是否推荐')->select([0 => '否', 1 => '是']);
            $show->field('is_on','是否上架')->select([0 => '否', 1 => '是']);
            $show->field('visit_count','浏览人数');
            $show->field('buyer_count','联系人数');
            $show->field('reviewer','审核人');
            $show->field('updated_at','审核时间');
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
            $form->display('number');
            $form->text('name');
            $form->text('method');
            $form->text('introduction');
            $form->text('adder_id');
            $form->text('status');
            $form->text('reviewer_id');
            $form->text('reason');
            $form->radio('is_recommend','是否推荐')->options([0 => '否', 1 => '是']);

            $form->display('created_at');
            $form->display('updated_at');
        });
    }
}
