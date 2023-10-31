<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
	use HasDateTimeFormatter;
    protected $guarded = [];

    public function teaching_method()
    {
        return $this->hasOne(TeachingMethod::class,'name','method');
    }

    public function teaching_type()
    {
        return $this->hasOne(TeachingType::class,'name','type');
    }

    public function teaching_subject()
    {
        return $this->hasOne(Subject::class,'name','subject');
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class,'organ_id');
    }

    public function collects()
    {
        return $this->hasMany(Collect::class, 'course_id');
    }

    // 所属机构
    public function users()
    {
        return $this->belongsToMany(User::class,'deliver_log','course_id','user_id');
    }

    public function deliver()
    {
        return $this->hasMany(DeliverLog::class,'course_id');
    }

    // 教师接单
    public function teacher_course()
    {
        return $this->belongsToMany(User::class,'teacher_course_orders','course_id','user_id');
    }

    public function adder()
    {
        return $this->belongsTo(User::class,'adder_id');
    }

    public function province_info()
    {
        return $this->belongsTo(Region::class,'province');
    }

    public function city_info()
    {
        return $this->belongsTo(Region::class,'city');
    }

    public function district_info()
    {
        return $this->belongsTo(Region::class,'district');
    }
}
