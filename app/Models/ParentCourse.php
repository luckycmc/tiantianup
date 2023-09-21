<?php

namespace App\Models;

use Dcat\Admin\Models\Administrator;
use Dcat\Admin\Traits\HasDateTimeFormatter;

use Illuminate\Database\Eloquent\Model;

class ParentCourse extends Model
{
	use HasDateTimeFormatter;
    protected $table = 'parent_courses';

    // 审核人
    public function reviewer()
    {
        return $this->belongsTo(Administrator::class,'reviewer_id');
    }
    
}
