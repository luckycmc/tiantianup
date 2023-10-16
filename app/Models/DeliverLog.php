<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;

use Illuminate\Database\Eloquent\Model;

class DeliverLog extends Model
{
	use HasDateTimeFormatter;
    protected $table = 'deliver_log';
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }

    public function course()
    {
        return $this->belongsTo(Course::class,'course_id');
    }

    public function teacher_info()
    {
        return $this->belongsTo(User::class,'user_id');
    }

    public function teacher_education()
    {
        return $this->hasMany(TeacherEducation::class,'user_id','user_id');
    }

    public function teacher_experience()
    {
        return $this->hasMany(TeacherCareer::class,'user_id','user_id');
    }

    // 教师信息
    public function teacher_detail()
    {
        return $this->hasOne(TeacherInfo::class,'user_id','user_id');
    }
    
}
