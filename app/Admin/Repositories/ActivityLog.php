<?php

namespace App\Admin\Repositories;

use App\Models\ActivityLog as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class ActivityLog extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
