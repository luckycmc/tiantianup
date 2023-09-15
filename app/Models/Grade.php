<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;

use Illuminate\Database\Eloquent\Model;

class Grade extends Model
{
	use HasDateTimeFormatter;
    protected $table = 'grade';
    
}
