<?php

namespace App\Admin\Controllers;

use App\Admin\Repositories\ServicePrice;
use App\Models\Area;
use App\Models\Region;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;
use Illuminate\Support\Str;

class EntryServicePriceController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new ServicePrice(), function (Grid $grid) {
            $grid->model()->where('type',3);
            $grid->column('id')->sortable();
            $grid->column('price','服务费');
            $grid->column('start_time','开始时间');
            $grid->column('end_time','结束时间');
            $grid->column('region','地区')->display(function ($region) {
                $name = [];
                $ids = $this->areas->pluck('id');
                foreach ($ids as $v) {
                    $name[] = Region::where('id',$v)->value('region_name');
                }
                $result = implode(',',$name);
                return Str::limit($result,30,'...');
            });
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
        return Show::make($id, new ServicePrice(), function (Show $show) {
            $show->field('id');
            $show->field('price');
            $show->field('type');
            $show->field('start_time');
            $show->field('end_time');
            $show->field('province');
            $show->field('city');
            $show->field('district');
            $show->field('adder');
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
        return Form::make(new ServicePrice(['areas']), function (Form $form) {
            $model = new Area();
            $form->display('id');
            $form->text('price','服务费');
            $form->hidden('type')->default(3);
            $form->dateRange('start_time','end_time','有效期');
            $form->tree('areas','执行地区')
                ->setTitleColumn('region_name')
                ->nodes(function () {
                    $areaModel = new Area();
                    return $areaModel->allNodes();
                })
                ->customFormat(function ($v) {
                    if (!$v) {
                        return [];
                    }
                    // dd(array_column($v,'id'));
                    return array_column($v, 'id');
                })
                ->expand(false);
            $form->text('adder','添加人');

            $form->display('created_at');
            $form->display('updated_at');
        });
    }
}
