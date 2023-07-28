<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;

use Illuminate\Database\Eloquent\Model;

class ParentStudent extends Model
{
	use HasDateTimeFormatter;
    protected $table = 'parent_students';
    
}
