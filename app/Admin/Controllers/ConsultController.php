<?php

namespace App\Admin\Controllers;

use App\Admin\Repositories\Consult;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class ConsultController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new Consult(['adder','editor']), function (Grid $grid) {
            $grid->column('consult_time','咨询时间');
            $grid->column('username');
            $grid->column('mobile');
            $grid->column('type','咨询方式')->using([0 => '热线咨询',1 => '在线咨询']);
            $grid->column('method','咨询类型')->using([0 => '咨询',1 => '投诉', 2 => '建议']);
            $grid->column('content');
            $grid->column('adder.username','添加人');
            $grid->column('editor.username','修改人');

            $grid->column('created_at','添加时间');
            $grid->column('updated_at','修改时间')->sortable();
        
            $grid->filter(function (Grid\Filter $filter) {
                $filter->like('mobile');
                $filter->like('username');
                $filter->whereBetween('consult_time',function ($q) {
                    $start = $this->input['start'] ?? null;
                    $end = $this->input['end'] ?? null;
                    $q->where('consult_time', '>=', $start);
                    $q->where('consult_time', '<=', $end);
                })->datetime();
            });
            $grid->disableDeleteButton();
            $grid->disableViewButton();
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
        return Show::make($id, new Consult(), function (Show $show) {
            $show->field('id');
            $show->field('username');
            $show->field('mobile');
            $show->field('type');
            $show->field('content');
            $show->field('adder_id');
            $show->field('editer_id');
            $show->field('consult_time');
            $show->field('organ_id');
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
        return Form::make(new Consult(), function (Form $form) {
            $form->display('id');
            $form->text('username');
            $form->text('mobile');
            $form->text('type');
            $form->select('method')->options();
            $form->text('content');
            $form->text('adder_id');
            $form->text('editer_id');
            $form->text('consult_time');
            $form->text('organ_id');
        
            $form->display('created_at');
            $form->display('updated_at');
        });
    }
}
