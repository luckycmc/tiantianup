<?php

namespace App\Admin\Repositories;

use App\Models\Agreement as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class Agreement extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
