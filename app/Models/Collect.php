<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;

use Illuminate\Database\Eloquent\Model;

class Collect extends Model
{
	use HasDateTimeFormatter;
    protected $table = 'collect';

    public function collect()
    {
        return $this->morphTo();
    }

    // 教师信息
    public function teacher()
    {
        return $this->belongsTo(User::class,'teacher_id');
    }
    
    // 课程信息
    public function course()
    {
        return $this->belongsTo(Course::class,'course_id');
    }

    // 教师详情
    public function teacher_info()
    {
        return $this->belongsTo(TeacherInfo::class, 'teacher_id', 'user_id');
    }

    public function teacher_education()
    {
        return $this->belongsTo(TeacherEducation::class,'teacher_id','user_id');
    }

    // 教师经历
    public function teacher_career()
    {
        return $this->hasMany(TeacherCareer::class, 'user_id', 'teacher_id');
    }

    // 机构信息
    public function course_organ()
    {
        return $this->belongsTo(Organization::class,'course_id','id');
    }
}
