<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;

use Illuminate\Database\Eloquent\Model;

class BaseInformation extends Model
{
	use HasDateTimeFormatter;
    protected $table = 'base_information';
    
}
