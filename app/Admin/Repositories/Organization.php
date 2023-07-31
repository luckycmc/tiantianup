<?php

namespace App\Admin\Repositories;

use App\Models\Organization as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class Organization extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
