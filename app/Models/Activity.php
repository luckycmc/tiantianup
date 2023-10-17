<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;

use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
	use HasDateTimeFormatter;

    // 奖励
    public function rewards()
    {
        return $this->hasOne(ActiveReward::class,'active_id','id');
    }
}
