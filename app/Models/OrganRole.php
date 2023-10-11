<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;

use Illuminate\Database\Eloquent\Model;

class OrganRole extends Model
{
	use HasDateTimeFormatter;
    protected $table = 'organ_roles';

    public function privileges()
    {
        return $this->belongsToMany(OrganPrivilege::class,'organ_role_privilege','role_id','privilege_id');
    }

    public function users()
    {
        return $this->belongsTo(User::class, 'id', 'organ_role_id');
    }
    
}
