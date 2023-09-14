<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\Grid\Recommend;
use App\Admin\Repositories\TeacherImage;
use App\Admin\Repositories\TeacherInfo;
use App\Admin\Repositories\User;
use App\Models\TeacherCareer;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Layout\Content;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;
use Dcat\Admin\Widgets\Tab;
use Illuminate\Support\Facades\Log;

class TeacherVerifyController extends AdminController
{
    public function index(Content $content)
    {
        $tab = Tab::make();
        return $content->title('认证审核')
            ->body($tab->add('实名认证',$this->real_auth_list())
                ->add('教师资料','教师资料')->withCard()
            );
    }

    protected function real_auth_list() {
        return Grid::make(new TeacherInfo('teacher'),function (Grid $grid) {
            $grid->model()->where('status',0);
            $grid->column('teacher.number','ID');
            $grid->column('teacher.name','教师姓名');
            $grid->column('teacher.gender','性别')->using([0 => '女',1 => '男']);
            $grid->column('teacher.mobile','手机号');
            $grid->column('created_at','提交时间');
            $grid->disableDeleteButton();
            $grid->disableCreateButton();
            $grid->disableEditButton();
        });
    }
    
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new User('teacher_info'), function (Grid $grid) {
            $grid->column('number','ID');
            $grid->column('name','教师姓名');
            $grid->column('gender','性别')->using([0 => '女',1 => '男']);
            $grid->column('mobile','手机号');
            $grid->column('created_at','提交时间');
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
        return Show::make($id, new User(['teacher_info','province','city','district','teacher_tags']), function (Show $show) {
            $show->field('number','ID');
            $show->field('name','教师姓名');
            $show->field('gender','性别')->using([0 => '女',1 => '男']);
            $show->field('mobile','手机号');
            $show->field('status','账号状态')->using([0 => '未注册', 1 => '正常', 2 => '禁用']);
            $show->field('region','所在省市区')->as(function () {
                return $this->province->region_name.$this->city->region_name.$this->district->region_name;
            });
            $show->field('teacher_info.highest_education','学历');
            $show->field('is_real_auth','实名认证状态')->using([0 => '未实名', 1 => '已实名']);
            $show->field('has_teacher_cert','是否有教师资格证')->using([0 => '否',1 => '是']);
            $show->field('teacher_info.status','审核状态')->using([0 => '待审核', 1 => '审核通过', 2 => '拒绝']);
            $show->field('is_recommend','推荐')->using([0 => '否', 1 => '是']);
            $show->field('teacher_tags','教师标签')->as(function ($teacher_tags) {
                return implode(',', collect($teacher_tags)->pluck('tag')->all());
            });
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Form::make(new User(['teacher_info','province','city','district']), function (Form $form) {
            if ($form->isEditing()) {
                $form->display('number','ID');
            }
            $form->text('name','姓名');
            $form->select('gender','性别')->options([1 => '男',0 => '女']);
            $form->mobile('mobile','手机号');
            $form->date('birthday','生日');
            $form->select('province_id','省份')->options('/api/city')->load('city_id', '/api/city');
            $form->select('city_id','城市')->options('/api/city')->load('district_id', '/api/city');
            $form->select('district_id','区县');
            $form->text('address','详细地址');
            $form->text('introduction','个人简介');
            if ($form->isCreating()) {
                $form->hidden('role')->value(3);
                $form->hidden('is_recommend')->value(0);
            }
            $form->display('created_at');
            $form->display('updated_at');
            $form->saved(function (Form $form, $result) {
                // 判断是否是新增操作
                if ($form->isCreating()) {
                    // 自增ID
                    $user_id = $result;
                    $user_info = \App\Models\User::find($user_id);
                    $city_id = $user_info->city_id;
                    $user_info->number = create_user_number($city_id,$user_id);
                    $user_info->save();
                }
            });
        });
    }
}
