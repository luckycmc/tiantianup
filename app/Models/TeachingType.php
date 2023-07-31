<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;

use Illuminate\Database\Eloquent\Model;

class TeachingType extends Model
{
	use HasDateTimeFormatter;
    protected $table = 'teaching_type';
    
}
