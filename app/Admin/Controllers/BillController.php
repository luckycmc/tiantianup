<?php

namespace App\Admin\Controllers;

use App\Admin\Repositories\Bill;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class BillController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new Bill('user'), function (Grid $grid) {
            $grid->model()->whereIn('type',[4,5]);
            $grid->column('id')->sortable();
            $grid->column('user.name','用户姓名');
            $grid->column('amount');
            $grid->column('type');
            $grid->column('description');
            $grid->column('status');
            $grid->column('created_at');
            $grid->column('updated_at')->sortable();
        
            $grid->filter(function (Grid\Filter $filter) {
                $filter->like('username');
                $filter->equal('role','用户类型')->select([
                    1 => '学生',2 => '家长',3 => '教师',4 => '机构'
                ]);
                $filter->whereBetween('updated_at', function ($q) {
                    $start = $this->input['start'] ?? null;
                    $end = $this->input['end'] ?? null;

                    if ($start !== null) {
                        $q->where('updated_at', '>=', $start);
                    }

                    if ($end !== null) {
                        $q->where('updated_at', '<=', $end);
                    }
                })->datetime();
        
            });
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
        return Show::make($id, new Bill(), function (Show $show) {
            $show->field('id');
            $show->field('user_id');
            $show->field('amount');
            $show->field('type');
            $show->field('description');
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
        return Form::make(new Bill(), function (Form $form) {
            $form->display('id');
            $form->text('user_id');
            $form->text('amount');
            $form->text('type');
            $form->text('description');
            $form->text('status');
        
            $form->display('created_at');
            $form->display('updated_at');
        });
    }
}
