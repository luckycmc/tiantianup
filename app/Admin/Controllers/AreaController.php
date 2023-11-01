<?php

namespace App\Admin\Controllers;

use App\Admin\Repositories\Area;
use App\Models\Region;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class AreaController extends AdminController
{
    /*public function index(Content $content)
    {
        return $content->header('运营城市')
            ->body(function (Row $row) {
                $tree = new Tree(new \App\Models\Area());
                $row->column(12, $tree);
                $tree->expand(false);
                $tree->maxDepth(1);
                $tree->disableCreateButton();
                $tree->branch(function ($branch) {
                    return "{$branch['region_name']}";
                });
                $tree->tools(function (Tree\Tools $tools) {
                    $tools->add(new CheckCity());
                });
            });
    }*/
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new \App\Models\Area(), function (Grid $grid) {
            $grid->region_name->tree(); // 开启树状表格功能
            $grid->column('is_checked','是否授权')->select([0 => '否', 1 => '是']);

            $grid->filter(function (Grid\Filter $filter) {
                $filter->like('region_name');
            });
            $grid->disableCreateButton();
            $grid->disableEditButton();
            $grid->disableDeleteButton();
            $grid->disableActions();
            $grid->disableBatchDelete();
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
        return Show::make($id, new Area(), function (Show $show) {
            $show->field('id');
            $show->field('region_name');
            $show->field('parent_id');
            $show->field('initial');
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
        return Form::make(new Region(), function (Form $form) {
            $form->radio('is_checked')->options([0 => '否', 1 => '是']);
        
            $form->display('created_at');
            $form->display('updated_at');
        });
    }
}
