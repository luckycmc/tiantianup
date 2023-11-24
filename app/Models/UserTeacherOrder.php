<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;

use Illuminate\Database\Eloquent\Model;

class UserTeacherOrder extends Model
{
	use HasDateTimeFormatter;
    protected $table = 'user_teacher_orders';

    public function teacher_info()
    {
        return $this->belongsTo(User::class,'teacher_id');
    }

    // 教师经历
    public function teacher_experience()
    {
        return $this->hasMany(TeacherCareer::class,'user_id','teacher_id');
    }

    // 教师信息
    public function teacher_detail()
    {
        return $this->hasOne(TeacherInfo::class,'user_id','teacher_id');
    }

    public function teacher_education()
    {
        return $this->belongsTo(TeacherEducation::class,'teacher_id','user_id');
    }
    
}
