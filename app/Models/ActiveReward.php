<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;

use Illuminate\Database\Eloquent\Model;

class ActiveReward extends Model
{
	use HasDateTimeFormatter;
    protected $table = 'active_reward';

    // 活动
    public function activity()
    {
        return $this->belongsTo(Activity::class);
    }
    
}
