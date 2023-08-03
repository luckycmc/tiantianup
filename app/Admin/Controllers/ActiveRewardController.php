<?php

namespace App\Admin\Controllers;

use App\Admin\Repositories\ActiveReward;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class ActiveRewardController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new ActiveReward(), function (Grid $grid) {
            $grid->column('id')->sortable();
            $grid->column('active_id');
            $grid->column('type');
            $grid->column('first_reward');
            $grid->column('second_reward');
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
        return Show::make($id, new ActiveReward(), function (Show $show) {
            $show->field('id');
            $show->field('active_id');
            $show->field('type');
            $show->field('first_reward');
            $show->field('second_reward');
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
        return Form::make(new ActiveReward(), function (Form $form) {
            $form->display('id');
            $form->text('active_id');
            $form->text('type');
            $form->text('first_reward');
            $form->text('second_reward');
        
            $form->display('created_at');
            $form->display('updated_at');
        });
    }
}
