<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\Grid\RefuseRealAuth;
use App\Admin\Actions\Grid\RejectRealAuth;
use App\Admin\Actions\Grid\VerifyRealAuth;
use App\Admin\Repositories\TeacherInfo;
use App\Admin\Repositories\TeacherRealAuth;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class TeacherRealAuthController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new TeacherInfo('teacher'), function (Grid $grid) {
            $grid->model()->orderByDesc('created_at');
            $grid->column('id')->sortable();
            $grid->column('teacher.name','教师姓名');
            $grid->column('id_card_front')->image('',60,60);
            $grid->column('id_card_backend')->image('',60,60);
            $grid->column('real_name');
            $grid->column('id_card_no');
            $grid->column('status','审核状态')->using([0 => '待审核', 1 => '已通过', 2 => '已拒绝']);
            $grid->column('reason');
            $grid->column('created_at');
            $grid->column('updated_at')->sortable();
            $grid->actions(function ($actions) {
                $status = $actions->row->status;
                if ($status == 0) {
                    $actions->append(new VerifyRealAuth());
                    $actions->append(new RefuseRealAuth());
                }
            });
            $grid->export()->rows(function ($rows) {
                foreach ($rows as &$row) {
                    $arr = ['待审核','通过','拒绝'];
                    $row['status'] = $arr[$row['status']];
                }
                return $rows;
            });
            $grid->filter(function (Grid\Filter $filter) {
                $filter->like('name');
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
                $filter->equal('status')->select([0 => '待审核', 1 => '已通过', 2 => '已拒绝']);
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
        return Show::make($id, new TeacherInfo(), function (Show $show) {
            $show->field('id');
            $show->field('user_id');
            $show->field('id_card_front')->image();
            $show->field('id_card_backend')->image();
            $show->field('real_name');
            $show->field('id_card_no');
            $show->field('status','审核状态')->using([0 => '待审核', 1 => '已通过', 2 => '已拒绝']);
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
        return Form::make(new TeacherInfo(), function (Form $form) {
            $form->display('id');
            $form->display('user_id');
            $form->image('id_card_front')->saveFullUrl();
            $form->image('id_card_backend')->saveFullUrl();
            $form->text('real_name');
            $form->text('id_card_no');
            $form->select('status','审核状态')->options([0 => '待审核',1 => '通过', 2 => '拒绝']);
            $form->text('reason');
        
            $form->display('created_at');
            $form->display('updated_at');
        });
    }
}
