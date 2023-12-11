<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\Grid\DisableTeacher;
use App\Admin\Actions\Grid\EnableTeacher;
use App\Admin\Actions\Grid\Recommend;
use App\Admin\Repositories\User;
use App\Models\TeacherCareer;
use App\Models\TeacherCert;
use App\Models\TeacherEducation;
use App\Models\TeacherInfo;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Layout\Content;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;
use Dcat\Admin\Widgets\Tab;
use Illuminate\Support\Facades\Log;

class TeacherInfoController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new User(['teacher_info','province','city','district','teacher_education']), function (Grid $grid) {
            $grid->model()->where('role',3);
            $grid->column('number','ID');
            $grid->column('name','教师姓名');
            $grid->column('gender','性别')->using([0 => '女',1 => '男']);
            $grid->column('mobile','手机号');
            $grid->column('status','账号状态')->select([0 => '未注册', 1 => '正常', 2 => '禁用']);
            $grid->column('region','所在省市区')->display(function () {
                return $this->province->region_name.$this->city->region_name.$this->district->region_name;
            });
            $grid->column('teacher_education.highest_education','学历');
            $grid->column('teacher_education.highest_education','所授科目');
            $grid->column('is_real_auth','实名认证状态')->using([0 => '未实名', 1 => '已实名']);
            $grid->column('has_teacher_cert','是否有教师资格证')->using([0 => '否',1 => '是']);
            $grid->column('teacher_info.status','审核状态')->using([0 => '待审核', 1 => '审核通过', 2 => '拒绝']);
            $grid->column('is_recommend','推荐')->select([0 => '否', 1 => '是']);
            $grid->column('updated_at','注册时间');

            $grid->filter(function (Grid\Filter $filter) {
                $filter->like('name');
                $filter->like('mobile','手机号码');
                $filter->equal('status','账号状态')->select([0 => '未注册', 1 => '正常', 2 => '禁用']);
                $filter->equal('teacher_info.highest_education','学历')->select('/api/education');
                $filter->equal('is_real_auth','实名认证')->select([0 => '未实名', 1 => '已实名']);
                $filter->equal('has_teacher_cert','是否有教师资格证')->radio([1 => '是', 0 => '否']);
                $filter->equal('province_id','省份')->select('/api/city')->load('city_id','/api/city');
                $filter->equal('city_id','城市')->select('/api/city')->load('district_id','/api/city');
                $filter->equal('district_id','区县')->select('/api/city');
                $filter->whereBetween('created_at',function ($q) {
                    $start = $this->input['start'] ?? null;
                    $end = $this->input['end'] ?? null;
                    $q->where('created_at', '>=', $start);
                    $q->where('created_at', '<=', $end);
                })->datetime();
                $filter->like('number','ID');
                $filter->equal('is_recommend','是否推荐')->radio([1 => '是', 0 => '否']);

            });
            $grid->disableDeleteButton();
            $grid->tools(function ($tools) {
                $tools->batch(function ($batch) {
                    $batch->add(new Recommend('批量推荐', 1));
                });
            });
            $grid->actions(function ($actions) {
                $status = $actions->row->status;
                if (!in_array($status,[2,3])) {
                    $actions->append(new DisableTeacher());
                } else {
                    $actions->append(new EnableTeacher());
                }
            });
            $grid->export()->rows(function ($rows) {
                foreach ($rows as &$row) {
                    Log::info('row: ',$row->toArray());
                    $arr = ['未注册','正常','禁用','永久禁用'];
                    $status_arr = ['待审核', '审核通过','拒绝'];
                    $row['gender'] = $row['gender'] == 1 ? '男' : '女';
                    $row['is_real_auth'] = $row['is_real_auth'] == 1 ? '是' : '否';
                    $row['status'] = $arr[$row['status']];
                    $row['has_teacher_cert'] = $row['is_real_auth'] == 1 ? '是' : '否';
                    $row['is_recommend'] = $row['is_recommend'] == 1 ? '是' : '否';
                    if ($row['teacher_info']) {
                        $row['teacher_info']['status'] = $status_arr[$row['teacher_info']['status']];
                    }
                    $row['region'] = $row->province->region_name.$row->city->region_name.$row->district->region_name;
                }
                return $rows;
            });
        });
    }

    public function show($id,Content $content) {
        $tab = Tab::make();
        $tab->add('基本信息',$this->detail($id))
            ->add('实名认证',$this->real_auth($id));
        if (TeacherCert::where('user_id',$id)->value('id')) {
            $tab->add('资格证书',$this->cert($id));
        }
        if (TeacherEducation::where('user_id',$id)->value('id')) {
            $tab->add('教育经历',$this->education($id));
        }
        $tab->add('教学经历',$this->career($id))
            ->add('教师风采',$this->images($id));
        return $content->title('教师详情')->body($tab);
    }

    // 实名认证
    private function real_auth($id) {
        return Show::make($id,new User(['teacher_info']),function (Show $show) {
            $show->field('is_real_auth','实名认证状态')->using([0 => '未实名', 1 => '已实名']);
            $show->field('teacher_info.id_card_front','身份证人像面')->image();
            $show->field('teacher_info.id_card_backend','身份证人像面')->image();
            $show->field('teacher_info.picture','免冠照片')->image();
        });
    }

    // 资格证书
    private function cert($id) {
        return Show::make($id,new TeacherCert(),function (Show $show) use ($id) {
            $show->field('teacher_cert','资格证书')->image();
            $show->field('honor_cert','荣誉证书')->as(function () {
                return json_decode($this->honor_cert);
            })->image();
            $show->field('other_cert','其他证书')->as(function () {
                return json_decode($this->other_cert);
            })->image();
        });
    }

    // 教育经历
    private function education($id) {
        $id = TeacherEducation::where('user_id',$id)->value('id');
        return Show::make($id,new User(['teacher_info']),function (Show $show) {
            $show->field('teacher_info.highest_education','最高学历');
            $show->field('teacher_info.graduate_school','毕业院校');
            $show->field('teacher_info.speciality','所学专业');
            $show->field('teacher_info.graduate_cert','毕业证书')->image();
            $show->field('teacher_info.diploma','学位证书')->image();
        });
    }

    // 教学经历
    private function career($id) {
        $careers = TeacherCareer::where('user_id',$id)->get();
        $html = '<table><thead><tr><th>所在单位</th><th>所授科目</th><th>授课对象</th><th>上课方式</th><th>开始时间</th><th>结束时间</th></tr></thead><tbody>';
        foreach ($careers as $career) {
            $html .= "<tr><td>{$career->organization}</td>";
            $html .= "<td>{$career->subject}</td>";
            $html .= "<td>{$career->object}</td>";
            $html .= "<td>{$career->teaching_type}</td>";
            $html .= "<td>{$career->start_time}</td>";
            $html .= "<td>{$career->end_time}</td>";
            $html .= "</tr>";
        }
        $html .= '</tbody></table>';
        return $html;
    }

    // 个人风采
    private function images($id) {
        $images = \App\Models\TeacherImage::where(['user_id' => $id,'type' => 2])->get();
        $html = '<div class="image-gallery">';
        foreach ($images as $image) {
            // 自定义图片样式等
            $html .= "<img style='width: 180px;height: 180px' src='{$image->url}' alt='教师风采' />";
        }
        $html .= '</div>';

        return $html;
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
            $show->field('avatar','头像')->image('',60,60);
            $show->field('gender','性别')->using([0 => '女',1 => '男']);
            $show->field('mobile','手机号');
            $show->field('status','账号状态')->using([0 => '未注册', 1 => '正常', 2 => '禁用']);
            $show->field('region','所在省市区')->as(function () {
                return $this->province->region_name.$this->city->region_name.$this->district->region_name;
            });
            $show->field('address','详细地址');
            $show->field('introduction','个人介绍');
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
            $form->select('is_recommend','是否推荐')->options([0 => '否', 1 => '是']);
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
