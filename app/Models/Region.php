<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;

use Dcat\Admin\Traits\ModelTree;
use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
    use ModelTree;
	use HasDateTimeFormatter;
    protected $orderColumn = 'region_type';
    protected $titleColumn = 'region_name';
    public $timestamps = false;

    public function parent()
    {
        return $this->belongsTo(Region::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Region::class, 'parent_id');
    }
}
