<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\Grid\RefuseCert;
use App\Admin\Actions\Grid\VerifyCert;
use App\Admin\Repositories\TeacherCert;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class TeacherCertController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new TeacherCert('user'), function (Grid $grid) {
            $grid->model()->orderByDesc('created_at');
            $grid->column('user.number','教师编号');
            $grid->column('user.name','教师名称');
            $grid->column('teacher_cert')->display(function ($teacher_cert) {
                return json_decode($teacher_cert,true);
            })->image('',60,60);
            $grid->column('other_cert')->display(function ($other_cert) {
                return json_decode($other_cert,true);
            })->image('',60,60);
            $grid->column('honor_cert')->display(function ($honor_cert) {
                return json_decode($honor_cert,true);
            })->image('',60,60);
            $grid->column('status')->using([0 => '待审核', 1 => '已通过', 2 => '已拒绝']);
            $grid->column('reason','拒绝原因');
            $grid->column('created_at');
            $grid->column('updated_at')->sortable();

            $grid->filter(function (Grid\Filter $filter) {
                $filter->like('user.name','教师名称');
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

            $grid->actions(function ($actions) {
                $status = $actions->row->status;
                if ($status == 0) {
                    $actions->append(new VerifyCert());
                    $actions->append(new RefuseCert());
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
        return Show::make($id, new TeacherCert(), function (Show $show) {
            $show->field('id');
            $show->field('teacher_id');
            $show->field('teacher_cert')->image();
            $show->field('other_cert')->image();
            $show->field('honor_cert')->image();
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
        return Form::make(new TeacherCert(), function (Form $form) {
            $form->display('id');
            $form->text('teacher_id');
            $form->text('teacher_cert');
            $form->text('other_cert');
            $form->text('honor_cert');
            $form->text('status');
        
            $form->display('created_at');
            $form->display('updated_at');
        });
    }
}
