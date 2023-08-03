<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;

use Illuminate\Database\Eloquent\Model;

class TeacherTag extends Model
{
	use HasDateTimeFormatter;
    protected $table = 'teacher_tags';
    protected $guarded = [];
    public $timestamps = false;

}
