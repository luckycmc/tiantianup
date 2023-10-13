<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;

use Illuminate\Database\Eloquent\Model;

class PlatformMessage extends Model
{
	use HasDateTimeFormatter;
    protected $table = 'platform_message';
    
}
