<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
	use HasDateTimeFormatter;

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
        return $this->belongsTo(User::class,'organ_id','id');
    }
}
