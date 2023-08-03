<?php

namespace App\Admin\Repositories;

use App\Models\DeliverLog as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class DeliverLog extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
