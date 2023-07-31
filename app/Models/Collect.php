<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;

use Illuminate\Database\Eloquent\Model;

class Collect extends Model
{
	use HasDateTimeFormatter;
    protected $table = 'collect';

    public function collect()
    {
        return $this->morphTo();
    }
    
}
