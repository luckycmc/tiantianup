<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\Grid\DisableActivity;
use App\Admin\Actions\Grid\UnDisableActivity;
use App\Admin\Repositories\Activity;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class DealActivityController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new Activity(), function (Grid $grid) {
            $grid->model()->where('type',3);
            $grid->column('id','活动id')->sortable();
            $grid->column('name','活动名称');
            $grid->column('status','活动状态')->using([0 => '已结束',1 => '进行中',2 => '待开始',3 => '已拒绝', 4 => '待审核', 5 => '禁用']);
            $grid->column('start_time','开始时间');
            $grid->column('end_time','结束时间');
            $grid->column('object','活动对象')->display(function ($object) {
                $arr = explode($object,true);
                if (count($arr) == 4) {
                    return '全部';
                }
                return $object;
            });
            $grid->column('type','活动类型')->using([1 => '邀新活动',2 => '教师注册活动',3 => '成交活动']);
            $grid->column('adder.name','创建人');
            $grid->column('created_at','创建时间');

            $grid->filter(function (Grid\Filter $filter) {
                $filter->like('name');
                $filter->equal('status','状态')->select([
                    0 => '已结束',1 => '进行中',2 => '待开始',3 => '已拒绝', 4 => '待审核', 5 => '禁用'
                ]);
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
            });
            $grid->actions(function ($actions) {
                $status = $actions->row->status;
                if ($status !== 5) {
                    $actions->append(new DisableActivity());
                } else {
                    $actions->append(new UnDisableActivity());
                }
            });
            $grid->export()->rows(function ($rows) {
                foreach ($rows as $index => &$row) {
                    $arr = ['已结束','进行中','待开始','已拒绝','待审核','禁用'];
                    $row['status'] = $arr[$row['status']];
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
        return Show::make($id, new Activity(), function (Show $show) {
            $show->field('id');
            $show->field('name');
            $show->field('image');
            $show->field('object');
            $show->field('type');
            $show->field('description');
            $show->field('reward');
            $show->field('introduction');
            $show->field('start_time');
            $show->field('end_time');
            $show->field('status');
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
        return Form::make(new Activity(), function (Form $form) {
            $form->display('id');
            $form->text('name');
            $form->image('image','图片')->saveFullUrl()->saving(function ($value) {
                $arr = explode('?',$value);
                return $arr[0];
            });
            $form->checkbox('object','对象')->options(['家长' => '家长', '教师' => '教师', '机构' => '机构'])->saving(function ($value) {
                return implode(',',$value);
            })->canCheckAll()->when(['教师'],function (Form $form) {
                $form->radio('teacher_deal_reward_type','教师奖励类型')->options(['现金' => '现金'])->default('现金');
                $form->number('deal_teacher_reward','奖励额度');
            })->when(['家长'],function (Form $form) {
                $form->radio('parent_deal_reward_type','家长奖励类型')->options(['现金' => '现金'])->default('现金');
                $form->number('parent_deal_reward','奖励额度');
            })->when(['机构'],function (Form $form) {
                $form->radio('organ_deal_reward_type','机构奖励类型')->options(['现金' => '现金'])->default('现金');
                $form->number('organ_deal_reward','奖励额度');
            });
            $form->hidden('type','类型')->default(3);
            $form->text('description','描述');
            $form->text('introduction','介绍');
            $form->dateRange('start_time','end_time','活动时间');
            $form->select('status','状态')->options([0 => '已结束',1 => '进行中',2 => '待开始',3 => '已拒绝', 4 => '待审核', 5 => '禁用']);

            $form->display('created_at');
            $form->display('updated_at');
        });
    }
}
