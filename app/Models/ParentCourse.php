<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;

use Illuminate\Database\Eloquent\Model;

class ParentCourse extends Model
{
	use HasDateTimeFormatter;
    protected $table = 'parent_courses';
    
}
