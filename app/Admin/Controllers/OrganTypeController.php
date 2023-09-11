<?php

namespace App\Admin\Controllers;

use App\Admin\Repositories\OrganType;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;
use Illuminate\Support\Facades\DB;

class OrganTypeController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new OrganType(), function (Grid $grid) {
            $grid->column('id')->sortable();
            $grid->column('name');
            $grid->column('update_at');
            $grid->column('created_at');
        
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
        return Show::make($id, new OrganType(), function (Show $show) {
            $show->field('id');
            $show->field('name');
            $show->field('update_at');
            $show->field('created_at');
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Form::make(new OrganType(), function (Form $form) {
            $form->display('id');
            $form->text('name');
            $form->text('update_at');
            $form->text('created_at');
        });
    }

    public function list()
    {
        $data = DB::table('organ_type')->select('name as id','name as text')->get();
        if (!$data) {
            return [];
        }
        return $data;
    }
}
