<?php

namespace App\Admin\Renderable;

use App\Models\Activity;
use Dcat\Admin\Grid;
use Dcat\Admin\Grid\LazyRenderable;
use Dcat\Admin\Models\Administrator;

class ActivityLog extends LazyRenderable
{
    public function grid(): Grid
    {
        $id = $this->payload['key'];
        $type = $this->payload['type'];
        return Grid::make(new \App\Models\ActivityLog(), function (Grid $grid) use ($id,$type) {
            $grid->model()->where('activity_id',$id);
            $grid->column('id')->sortable();
            if ($type == 1) {
                $grid->column('username');
                $grid->column('role','用户类型')->using([1 => '学生',2 => '家长',3 => '教师', 4 => '机构']);
                $grid->column('first_child','一级下线人数');
                $grid->column('second_child','二级下线人数');
                $grid->export();
            } else if ($type == 2) {
                $grid->column('username','教师姓名');
                $grid->column('project','完成项目');
                $grid->column('amount','发放金额');
                $grid->column('description','发放描述');
                $grid->column('created_at','发放时间');
            } else {
                $grid->column('number','用户编号');
                $grid->column('username');
                $grid->column('role','用户类型')->using([1 => '学生',2 => '家长',3 => '教师', 4 => '机构']);
                $grid->column('deal_amount','成交数量');
                $grid->column('amount','活动金额');
            }
            $grid->filter(function (Grid\Filter $filter) use ($type) {
                if ($type == 1 || $type == 3) {
                    $filter->equal('role','用户类型')->select([1 => '学生',2 => '家长',3 => '教师', 4 => '机构']);
                }
                if ($type == 2) {
                    $filter->like('username','用户名');
                    $filter->whereBetween('created_at',function ($q) {
                        $start = $this->input['start'] ?? null;
                        $end = $this->input['end'] ?? null;
                        if ($start !== null) {
                            $q->where('created_at', '>=', $start);
                        }
                        if ($end !== null) {
                            $q->where('created_at', '<=', $end);
                        }
                    },'发放时间');
                }
            });
            $grid->disableActions();
        });
    }
}