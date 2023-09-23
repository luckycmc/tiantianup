<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;

use Illuminate\Database\Eloquent\Model;

class TeacherCert extends Model
{
	use HasDateTimeFormatter;
    protected $table = 'teacher_cert';
    
}
