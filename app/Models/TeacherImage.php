<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;

use Illuminate\Database\Eloquent\Model;

class TeacherImage extends Model
{
	use HasDateTimeFormatter;
    protected $table = 'teacher_images';
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
}
