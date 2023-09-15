<?php

namespace App\Models;

use Dcat\Admin\Models\Administrator;
use Dcat\Admin\Traits\HasDateTimeFormatter;

use Illuminate\Database\Eloquent\Model;

class Consult extends Model
{
	use HasDateTimeFormatter;

    public function adder()
    {
        return $this->belongsTo(Administrator::class,'adder_id');
    }

    public function editor()
    {
        return $this->belongsTo(Administrator::class,'editor_id');
    }
}
