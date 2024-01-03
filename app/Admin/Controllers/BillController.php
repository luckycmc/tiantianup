<?php

namespace App\Admin\Controllers;

use App\Admin\Repositories\Bill;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Log;

class BillController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new Bill(['user']), function (Grid $grid) {
            $grid->model()->whereIn('type',[4,5]);
            $grid->column('id')->sortable();
            $grid->column('user.name','用户姓名')->display(function () {
                // dd($this->user->role);
                if ($this->user->role == 4) {
                    return $this->user->organization->name;
                } else {
                    return $this->user->name;
                }
            });
            $grid->column('user.role','用户身份')->using([1 => '学生', 2 => '家长', 3 => '教师', 4 => '机构']);
            $grid->column('amount');
            $grid->column('discount');
            $grid->column('type')->using([4 => '购买课程', 5 => '查看教师']);
            $grid->column('description');
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
                    Log::info('row: ',$row->toArray());
                    $arr = ['已结束','进行中','待开始','已拒绝','待审核'];
                    $type_arr = ['','余额提现','邀新奖励','活动奖励','购买课程','查看教师','审核教学经历','审核证书','审核教师风采','成交','查看报名','查看中介单'];
                    if (isset($row['status'])) {
                        $row['status'] = $arr[$row['status']];
                    } else {
                        $row['status'] = '/';
                    }
                    $role_arr = ['','学生','家长','教师','机构'];
                    $row['role'] = $role_arr[$row['role']];
                    // $row['user.name'] = $row->user->role == 4 ? $row->user->organization->name : $row->user->name;

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
