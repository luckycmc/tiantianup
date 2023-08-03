<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;

use Illuminate\Database\Eloquent\Model;

class TeacherInfo extends Model
{
	use HasDateTimeFormatter;
    protected $table = 'teacher_info';
    protected $guarded = [];

    public function teacher()
    {
        return $this->belongsTo(User::class);
    }
    
}
