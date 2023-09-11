<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;

use Illuminate\Database\Eloquent\Model;

class OrganType extends Model
{
	use HasDateTimeFormatter;
    protected $table = 'organ_type';
    public $timestamps = false;

}
