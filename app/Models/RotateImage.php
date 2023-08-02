<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;

use Illuminate\Database\Eloquent\Model;

class RotateImage extends Model
{
	use HasDateTimeFormatter;
    protected $table = 'rotate_images';
    
}
