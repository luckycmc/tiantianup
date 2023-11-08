<?php

namespace App\Admin\Controllers;

use App\Admin\Repositories\Constant;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;
use Illuminate\Support\Facades\DB;

class ConstantController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new Constant(), function (Grid $grid) {
            $grid->column('id')->sortable();
            $grid->column('name');
            $grid->column('type')->using([
                1 => '人事状态',
                2=> '机构类型',
                3 => '机构性质',
                4 => '课程类型',
                5 => '科目',
                6 => '上课方式',
                7 => '授课对象',
                8 => '年级',
                9 => '信息来源',
                10 => '随行人员类型',
                11 => '到访目的',
                12 => '联系人关系',
                13 => '培训类型',
            ]);
            $grid->column('created_at');
            $grid->column('updated_at')->sortable();
        
            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('id');
        
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
        return Show::make($id, new Constant(), function (Show $show) {
            $show->field('id');
            $show->field('name');
            $show->field('type');
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
        return Form::make(new Constant(), function (Form $form) {
            $form->display('id');
            $form->text('name');
            $form->select('type')->options([
                1 => '人事状态',
                2=> '机构类型',
                3 => '机构性质',
                4 => '课程类型',
                5 => '科目',
                6 => '上课方式',
                7 => '授课对象',
                8 => '年级',
                9 => '信息来源',
                10 => '随行人员类型',
                11 => '到访目的',
                12 => '联系人关系',
                13 => '培训类型',
            ]);
        
            $form->display('created_at');
            $form->display('updated_at');
        });
    }

    // 培训类型
    public function training_type()
    {
        $training_type = DB::table('constant')->where('type',6)->select('id','name as text')->get();
        if (!isset($training_type)) {
            return [];
        }
        return $training_type;
    }

    // 机构类型
    public function organ_type()
    {
        $organ_type = DB::table('constant')->where('type',2)->select('id','name as text')->get();
        if (!isset($organ_type)) {
            return [];
        }
        return $organ_type;
    }

    // 机构性质
    public function nature()
    {
        $nature = DB::table('constant')->where('type',3)->select('id','name as text')->get();
        if (!isset($nature)) {
            return [];
        }
        return $nature;
    }
}
