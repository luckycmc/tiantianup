<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;

use Illuminate\Database\Eloquent\Model;

class TeachingMethod extends Model
{
	use HasDateTimeFormatter;
    protected $table = 'teaching_methods';
    public $timestamps = false;

}
