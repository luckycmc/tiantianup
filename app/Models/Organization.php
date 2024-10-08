<?php

namespace App\Models;

use Dcat\Admin\Models\Administrator;
use Dcat\Admin\Traits\HasDateTimeFormatter;

use Illuminate\Database\Eloquent\Model;

class Organization extends Model
{
	use HasDateTimeFormatter;
    protected $guarded = [];

    public function province()
    {
        return $this->hasOne(Region::class,'id','province_id');
    }

    public function city()
    {
        return $this->hasOne(Region::class,'id','city_id');
    }

    public function district()
    {
        return $this->hasOne(Region::class,'id','district_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer()
    {
        return $this->hasOne(Administrator::class,'id','reviewer_id');
    }

    public function image_info()
    {
        return $this->hasMany(OrganImage::class,'organ_id');
    }
}
