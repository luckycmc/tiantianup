<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\Grid\PayWithdraw;
use App\Admin\Actions\Grid\RefuseWithdraw;
use App\Admin\Actions\Grid\VerifyWithdraw;
use App\Admin\Repositories\Withdraw;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class WithdrawController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new Withdraw(), function (Grid $grid) {
            $grid->column('id','提现id')->sortable();
            $grid->column('username','用户名');
            $grid->column('role','用户类型')->using([1 => '学生',2 => '家长',3 => '教师',4 => '机构']);
            $grid->column('type','提现类型')->using([1 => '支付宝',2 => '微信', 3 => '银行卡']);
            $grid->column('mobile');
            $grid->column('status','状态')->using([0 => '审核中',1 => '审核未通过', 2 => '打款中', 3 => '已打款']);
            $grid->column('amount');
            $grid->column('account','账号');
            $grid->column('created_at','申请时间');
            $grid->column('updated_at')->sortable();
        
            $grid->filter(function (Grid\Filter $filter) {
                $filter->like('username');
                $filter->like('mobile','手机号');
                $filter->equal('role','用户类型')->select([
                    1 => '学生',2 => '家长',3 => '教师',4 => '机构'
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
                if ($status == 0) {
                    $actions->append(new VerifyWithdraw());
                    $actions->append(new RefuseWithdraw());
                }
                if ($status == 2) {
                    $actions->append(new PayWithdraw());
                }
            });
            $grid->export()->rows(function ($rows) {
                foreach ($rows as $index => &$row) {
                    $arr = ['审核中','审核未通过','打款中','已打款'];
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
        return Show::make($id, new Withdraw(), function (Show $show) {
            $show->field('id');
            $show->field('user_id');
            $show->field('amount');
            $show->field('type');
            $show->field('username');
            $show->field('mobile');
            $show->field('status');
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
        return Form::make(new Withdraw(), function (Form $form) {
            $form->display('id');
            $form->text('user_id');
            $form->text('amount');
            $form->text('type');
            $form->text('username');
            $form->text('mobile');
            $form->text('status');
            $form->text('reason');
        
            $form->display('created_at');
            $form->display('updated_at');
        });
    }
}
