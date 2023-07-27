<?php

namespace App\Admin\Repositories;

use App\Models\Region as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class Region extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
