<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;

use Illuminate\Database\Eloquent\Model;

class OrganUserRole extends Model
{
	use HasDateTimeFormatter;
    protected $table = 'organ_user_role';

    public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }

    public function role()
    {
        return $this->belongsTo(OrganRole::class,'role_id');
    }
    
}
