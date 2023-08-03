<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;

use Illuminate\Database\Eloquent\Model;

class DeliverLog extends Model
{
	use HasDateTimeFormatter;
    protected $table = 'deliver_log';
    protected $guarded = [];
    
}
