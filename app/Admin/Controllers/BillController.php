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
            $grid->model()->whereIn('type',[4,5,9,10,11])->orderByDesc('created_at');
            $grid->column('id')->sortable();
            $grid->column('user.name','用户姓名')->display(function () {
                // dd($this->user->role);
                if ($this->role == 4) {
                    return $this->user->organization ? $this->user->organization->name : null;
                } else {
                    return $this->user ? $this->user->name : null;
                }
            });
            $grid->column('user.number','用户编号');
            $grid->column('user.role','用户身份')->using([1 => '学生', 2 => '家长', 3 => '教师', 4 => '机构']);
            $grid->column('amount');
            $grid->column('discount');
            $grid->column('type')->using([4 => '购买课程', 5 => '查看教师', 9 => '成交', 10 => '查看报名', 11 => '查看中介单']);
            $grid->column('number');
            $grid->column('description');
            $grid->column('created_at');
            $grid->column('updated_at')->sortable();
        
            $grid->filter(function (Grid\Filter $filter) {
                $filter->like('username');
                $filter->equal('user.role','用户身份')->select([
                    1 => '学生',2 => '家长',3 => '教师',4 => '机构'
                ]);
                $filter->like('number');
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
                    if ($row->role > 0) {
                        $row['user']['role'] = $role_arr[$row->user->role];
                        $row['user']['name'] = $row->role == 4 ? ($row->user->organization ? $row->user->organization->name : null) : $row->user->name;
                    }
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
