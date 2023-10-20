<?php

namespace App\Admin\Repositories;

use App\Models\Area as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class Area extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
