<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;

use Illuminate\Database\Eloquent\Model;

class TeacherRealAuth extends Model
{
	use HasDateTimeFormatter;
    protected $table = 'teacher_real_auth';

    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
}
