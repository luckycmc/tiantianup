<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;

use Illuminate\Database\Eloquent\Model;

class Bill extends Model
{
	use HasDateTimeFormatter;

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
