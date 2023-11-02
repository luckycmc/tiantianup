<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\Show\RefuseActivity;
use App\Admin\Actions\Show\VerifyActivity;
use App\Admin\Repositories\Activity;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;
use Illuminate\Support\Str;

class ActivityController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new Activity(), function (Grid $grid) {
            $grid->column('id','活动id')->sortable();
            $grid->column('name','活动名称');
            $grid->column('status','活动状态')->using([0 => '已结束',1 => '进行中',2 => '待开始',3 => '已拒绝', 4 => '待审核', 5 => '已禁用']);
            $grid->column('start_time');
            $grid->column('end_time');
            $grid->column('object','活动对象')->display(function ($object) {
                $arr = explode(',',$object);
                if (count($arr) == 4) {
                    return '全部';
                }
                return $object;
            });
            $grid->column('type','活动类型')->using([1 => '邀新活动',2 => '教师注册活动',3 => '成交活动']);
            $grid->column('adder','创建人');
            $grid->column('created_at','创建时间');
        
            $grid->filter(function (Grid\Filter $filter) {
                $filter->like('name');
                $filter->whereBetween('created_at', function ($q) {
                    $start = $this->input['start'] ?? null;
                    $end = $this->input['end'] ?? null;
                    // dd($start,$end);

                    if ($start !== null) {
                        $q->where('created_at', '>=', $start);
                    }

                    if ($end !== null) {
                        $q->where('created_at', '<=', $end);
                    }
                })->datetime();
            });
            $grid->disableCreateButton();
            $grid->disableDeleteButton();
            $grid->disableEditButton();
            $grid->export()->rows(function ($rows) {
                foreach ($rows as &$row) {
                    $arr = ['已结束','进行中','待开始','已拒绝','待审核'];
                    $type_arr = ['','邀新活动','教师注册活动','成交活动'];
                    $row['status'] = $arr[$row['status']];
                    $row['type'] = $type_arr[$row['type']];
                    $row['is_disabled'] = $row['is_disabled'] == 0 ? '否' : '是';
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
        return Show::make($id, new Activity(), function (Show $show) use ($id) {
            $show->field('id');
            $show->field('name');
            $show->field('image')->image();
            $show->field('object');
            $show->field('type')->using([1 => '邀新活动',2 => '教师注册活动',3 => '成交活动']);
            $show->field('description');
            $info = \App\Models\Activity::where('id',$id)->select('type','object')->first();
            $type = $info->type;
            $object = $info->object;
            if ($type == 1) {
                if (Str::contains($object,'教师')) {
                    $show->field('teacher_reward_type');
                    $show->field('teacher_first_reward');
                    $show->field('teacher_second_reward');
                    $show->field('teacher_new_reward');
                }
                if (Str::contains($object,'家长')) {
                    $show->field('parent_reward_type');
                    $show->field('parent_first_reward');
                    $show->field('parent_second_reward');
                    $show->field('parent_new_reward');
                }
                if (Str::contains($object,'学生')) {
                    $show->field('student_reward_type');
                    $show->field('student_first_reward');
                    $show->field('student_second_reward');
                    $show->field('student_new_reward');
                }
                if (Str::contains($object,'机构')) {
                    $show->field('organ_reward_type');
                    $show->field('organ_first_reward');
                    $show->field('organ_second_reward');
                    $show->field('organ_new_reward');
                }
            } else if ($type == 2) {
                $show->field('teacher_real_auth_reward');
                $show->field('teacher_cert_reward');
                $show->field('teacher_career_reward');
                $show->field('teacher_image_reward');
            } else {
                if (Str::contains($object,'家长')) {
                    $show->field('parent_deal_reward_type');
                    $show->field('parent_deal_reward');
                }
                if (Str::contains($object,'教师')) {
                    $show->field('teacher_deal_reward_type');
                    $show->field('deal_teacher_reward');
                }
                if (Str::contains($object,'机构')) {
                    $show->field('organ_deal_reward_type');
                    $show->field('organ_deal_reward');
                }
            }
            $show->field('introduction');
            $show->field('start_time');
            $show->field('end_time');
            $show->field('status')->using([0 => '已结束',1 => '进行中',2 => '待开始',3 => '已拒绝', 4 => '待审核', 5 => '已禁用']);
            $show->field('created_at');
            $show->field('updated_at');
            $show->disableDeleteButton();
            $show->disableEditButton();
            $show->tools(function (Show\Tools $tools) {
                $tools->append(new VerifyActivity());
                $tools->append(new RefuseActivity());
            });
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Form::make(new Activity(), function (Form $form) {
            $form->display('id');
            $form->text('name');
            $form->text('image');
            $form->text('object');
            $form->text('type');
            $form->text('description');
            $form->text('reward');
            $form->text('introduction');
            $form->text('start_time');
            $form->text('end_time');
            $form->text('status');
        
            $form->display('created_at');
            $form->display('updated_at');
        });
    }
}
