<?php

namespace App\Admin\Controllers;

use App\Admin\Repositories\Region;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use function Symfony\Component\String\s;

class RegionController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new Region(), function (Grid $grid) {
            $grid->column('id')->sortable();
            $grid->column('parent_id');
            $grid->column('region_name');
            $grid->column('code');
            $grid->column('region_type');
            $grid->column('is_last');
        
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
        return Show::make($id, new Region(), function (Show $show) {
            $show->field('id');
            $show->field('parent_id');
            $show->field('region_name');
            $show->field('code');
            $show->field('region_type');
            $show->field('is_last');
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
            $form->display('id');
            $form->text('parent_id');
            $form->text('region_name');
            $form->text('code');
            $form->text('region_type');
            $form->text('is_last');
        });
    }

    public function city(Request $request)
    {
        $id = $request->get('q') ?? 0;
        $data = DB::table('regions')->where('parent_id',$id)->select('region_name as text','id')->get();
        if (!$data) {
            return [];
        }
        return $data;
    }
}
