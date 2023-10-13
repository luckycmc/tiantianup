<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;

use Illuminate\Database\Eloquent\Model;

class CourseSetting extends Model
{
	use HasDateTimeFormatter;
    protected $table = 'course_setting';
    
}
