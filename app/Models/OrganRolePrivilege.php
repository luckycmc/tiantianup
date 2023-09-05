<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;

use Illuminate\Database\Eloquent\Model;

class OrganRolePrivilege extends Model
{
	use HasDateTimeFormatter;
    protected $table = 'organ_role_privilege';
    
}
