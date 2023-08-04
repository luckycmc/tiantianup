<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;

use Illuminate\Database\Eloquent\Model;

class TeacherCareer extends Model
{
	use HasDateTimeFormatter;
    protected $table = 'teacher_career';
    protected $guarded = [];

    public function teacher()
    {
        return $this->belongsTo(User::class);
    }
    
}
