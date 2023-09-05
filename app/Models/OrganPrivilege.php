<?php

namespace App\Models;

use Dcat\Admin\Models\Role;
use Dcat\Admin\Traits\HasDateTimeFormatter;

use Illuminate\Database\Eloquent\Model;

class OrganPrivilege extends Model
{
	use HasDateTimeFormatter;
    protected $table = 'organ_privileges';

    public function roles()
    {
        return $this->belongsToMany(Role::class,'organ_role_privilege','privilege_id','role_id');
    }
    
}
