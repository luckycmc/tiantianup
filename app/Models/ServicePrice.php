<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;

use Illuminate\Database\Eloquent\Model;

class ServicePrice extends Model
{
	use HasDateTimeFormatter;
    protected $table = 'service_price';
    
}
