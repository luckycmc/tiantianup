<?php

namespace App\Admin\Controllers;

use App\Admin\Repositories\Consult;
use Dcat\Admin\Admin;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Models\Administrator;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;
use Illuminate\Support\Str;

class AllConsultController extends AdminController
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
            $grid->column('content')->display(function ($content) {
                return Str::limit($content,20,'...');
            });
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
                $filter->equal('type','咨询类型')->select([0 => '咨询',1 => '投诉', 2 => '建议']);
                $filter->equal('adder_id','添加人')->select('/api/admin_users');
            });
            $grid->disableDeleteButton();
            $grid->disableViewButton();
            $grid->export();
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
            $show->field('editor_id');
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
            $admin = Admin::user()->id;
            $form->display('id');
            $form->text('username')->required();
            $form->text('mobile')->required();
            $form->select('type')->options([0 => '热线咨询',1 => '在线咨询'])->required();
            $form->select('method')->options([0 => '咨询',1 => '投诉', 2 => '建议'])->required();
            $form->text('content')->required();
            if ($form->isCreating()) {
                $form->hidden('adder_id')->default($admin);
            }
            if ($form->isEditing()) {
                $form->hidden('editor_id')->default($admin);
            }
            $form->datetime('consult_time')->required();

            $form->display('created_at');
            $form->display('updated_at');
        });
    }
}
