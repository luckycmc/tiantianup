<?php

namespace App\Admin\Repositories;

use App\Models\TeachingType as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class TeachingType extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
