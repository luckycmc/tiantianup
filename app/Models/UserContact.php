<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;

use Illuminate\Database\Eloquent\Model;

class UserContact extends Model
{
	use HasDateTimeFormatter;
    protected $table = 'user_contacts';
    protected $guarded = [];
    
}
