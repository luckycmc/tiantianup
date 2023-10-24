<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PlatformMessage extends Model
{
	use HasDateTimeFormatter;
    protected $table = 'platform_message';

    public function saveMessage($name,$content,$platform)
    {
        $model = new self();
        $model->name = $name;
        $model->content = $content;
        $model->send_platform = $platform;
        $model->save();
    }
    
}
