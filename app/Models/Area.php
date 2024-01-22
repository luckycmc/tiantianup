<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;

use Dcat\Admin\Traits\ModelTree;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Area extends Model
{
	use HasDateTimeFormatter,
    ModelTree {
        ModelTree::boot as treeBoot;
    }
    protected $table = 'regions';
    protected $titleColumn = 'region_name';
    protected $parentColumn = 'parent_id';

    protected $orderColumn = 'region_type';
    protected $guarded = [];
    public function service_price(): BelongsToMany
    {
        return $this->belongsToMany(ServicePrice::class, 'service_price_area', 'area_id', 'service_price_id');
    }
}
