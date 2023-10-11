<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;

use Illuminate\Database\Eloquent\Model;

class UserCourse extends Model
{
	use HasDateTimeFormatter;
    protected $table = 'user_courses';
    protected $guarded = 1;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function scopeOrderByCreationTime($query)
    {
        return $query->orderBy('created_at');
    }
    
}
