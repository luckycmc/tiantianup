<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ServicePrice extends Model
{
	use HasDateTimeFormatter;
    protected $table = 'service_price';
    protected $guarded = [];


    public function areas(): BelongsToMany
    {
        return $this->belongsToMany(Area::class, 'service_price_area', 'service_price_id', 'area_id')->withTimestamps();
    }
    
}
