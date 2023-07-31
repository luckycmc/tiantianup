<?php

namespace App\Admin\Repositories;

use App\Models\TeachingMethod as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class TeachingMethod extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
