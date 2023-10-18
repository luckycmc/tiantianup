<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;

use Illuminate\Database\Eloquent\Model;

class OrganImage extends Model
{
	use HasDateTimeFormatter;
    protected $table = 'organ_images';
    
}
