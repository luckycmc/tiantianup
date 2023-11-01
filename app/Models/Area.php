<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;

use Dcat\Admin\Traits\ModelTree;
use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    use ModelTree;
	use HasDateTimeFormatter;
    protected $table = 'regions';
    protected $titleColumn = 'region_name';
    protected $parentColumn = 'parent_id';

    protected $orderColumn = 'region_type';
}
