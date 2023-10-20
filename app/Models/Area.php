<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;

use Dcat\Admin\Traits\ModelTree;
use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    use ModelTree;
	use HasDateTimeFormatter;
    protected $orderColumn = 'order';
    protected $titleColumn = 'region_name';
}
