<?php

namespace App\Admin\Controllers;

use App\Admin\Repositories\Notice;
use Dcat\Admin\Admin;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class NoticeController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new Notice(), function (Grid $grid) {
            $grid->column('id')->sortable();
            $grid->column('title');
            $grid->column('content');
            $grid->column('status')->using([0 => '关闭',1 => '开启']);
            $grid->column('object')->display(function ($object) {
                $states = [
                    '1' => '学生',
                    '2' => '家长',
                    '3' => '教师',
                    '4' => '机构',
                ];
                $nums = explode(',', $object); // 将数字组合转化为数组

                $chineseStates = []; // 存储对应的汉字状态

                foreach ($nums as $num) {
                    if (array_key_exists($num, $states)) {
                        $chineseStates[] = $states[$num]; // 将查询到的汉字状态存储到数组中
                    }
                }

                if (!empty($chineseStates)) {
                    $result = implode('、', $chineseStates); // 将数组中的汉字状态用顿号连接起来
                    return $result;
                } else {
                    return '';
                }
            });
            $grid->column('author');
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
        return Show::make($id, new Notice(), function (Show $show) {
            $show->field('id');
            $show->field('title');
            $show->field('content');
            $show->field('status');
            $show->field('object');
            $show->field('author');
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
        return Form::make(new Notice(), function (Form $form) {
            $form->display('id');
            $form->text('title');
            $form->editor('content');
            $form->checkbox('object','对象')->options(['1' => '学生', '2' => '家长', '3' => '教师', '4' => '机构'])->saving(function ($value) {
                return implode(',', $value);
            });
            $form->hidden('author')->value(Admin::user()->name);
            $form->hidden('status')->value(1);

            $form->display('created_at');
            $form->display('updated_at');
        });
    }
}
