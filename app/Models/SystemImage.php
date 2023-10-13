<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;

use Illuminate\Database\Eloquent\Model;

class SystemImage extends Model
{
	use HasDateTimeFormatter;
    protected $table = 'system_images';
    public $timestamps = false;

}
