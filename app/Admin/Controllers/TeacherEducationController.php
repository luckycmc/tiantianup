<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\Grid\RefuseEducation;
use App\Admin\Actions\Grid\VerifyEducation;
use App\Admin\Repositories\TeacherEducation;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class TeacherEducationController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new TeacherEducation(['user']), function (Grid $grid) {
            $grid->model()->orderByDesc('created_at');
            $grid->column('user.number','教师编号');
            $grid->column('user.name','教师姓名');
            $grid->column('highest_education');
            $grid->column('graduate_school');
            $grid->column('speciality');
            $grid->column('graduate_cert')->image('',60,60);
            $grid->column('diploma')->image('',60,60);
            $grid->column('status','状态')->using([0 => '待审核',1 => '通过', 2 => '拒绝']);
            $grid->column('created_at');
            $grid->column('updated_at')->sortable();

            $grid->filter(function (Grid\Filter $filter) {
                $filter->like('user.name','教师姓名');
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
                $filter->equal('status','状态')->select([0 => '待审核', 1 => '已通过', 2 => '已拒绝']);
            });
            $grid->actions(function ($actions) {
                $status = $actions->row->status;
                if ($status == 0) {
                    $actions->append(new VerifyEducation());
                    $actions->append(new RefuseEducation());
                }
            });
            $grid->export()->rows(function ($rows) {
                foreach ($rows as &$row) {
                    $arr = ['待审核','通过','拒绝'];
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
        return Show::make($id, new TeacherEducation(['user']), function (Show $show) {
            $show->field('id');
            $show->field('user.name','教师姓名');
            $show->field('highest_education');
            $show->field('graduate_school');
            $show->field('speciality');
            $show->field('graduate_cert')->image('',60,60);
            $show->field('diploma')->image('',60,60);
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
        return Form::make(new TeacherEducation(['user']), function (Form $form) {
            $form->display('id');
            $form->display('user.name');
            $form->text('highest_education');
            $form->text('graduate_school');
            $form->text('speciality');
            $form->image('graduate_cert')->saveFullUrl()->saving(function ($graduate_cert) {
                $arr = explode('?',$graduate_cert);
                return $arr[0];
            });
            $form->image('diploma')->saveFullUrl()->saving(function ($diploma) {
                $arr = explode('?',$diploma);
                return $arr[0];
            });
            $form->select('status')->options([0 => '待审核',1 => '通过', 2 => '拒绝']);
        
            $form->display('created_at');
            $form->display('updated_at');
        });
    }
}
