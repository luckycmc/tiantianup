<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\Tree\CheckCity;
use App\Admin\Repositories\Area;
use App\Models\Region;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Layout\Content;
use Dcat\Admin\Layout\Row;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;
use Dcat\Admin\Tree;

class AreaController extends AdminController
{
    public function index(Content $content)
    {
        return $content->header('运营城市')
            ->body(function (Row $row) {
                $tree = new Tree(new \App\Models\Area());
                $row->column(12, $tree);
                $tree->expand(false);
                $tree->maxDepth(3);
                $tree->disableCreateButton();
                $tree->branch(function ($branch) {
                    return "{$branch['region_name']}";
                });
            });
    }
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new Area(), function (Grid $grid) {
            $grid->column('id')->sortable();
            $grid->column('region_name');
            $grid->column('parent_id');
            $grid->column('order');
            $grid->column('initial');
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
        return Form::make(new Area(), function (Form $form) {
            // $form->display('id');
            $form->select('province_id')->options('/api/city')->load('city','/api/city');
            $form->select('city_id')->options('/api/city');
            $form->saving(function (Form $form) {
                // dd($form->city_id,$form->city);
                $region_type = Region::where('id');
                $form->deleteInput('city');
                $form->id = $form->city_id;
                $form->parent_id = $form->city_id;
                $form->region_name = Region::where('id',$form->id);
            });
            // $form->text('region_name');
            /*$form->select('parent_id', trans('admin.parent_id'))
                ->options(\App\Models\Area::selectOptions())
                ->saving(function ($v) {
                    return (int) $v;
                })->required();*/
            // $form->text('initial','首字母');
        
            $form->display('created_at');
            $form->display('updated_at');
        });
    }
}
