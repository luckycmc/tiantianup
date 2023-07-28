<?php

namespace App\Admin\Repositories;

use App\Models\ParentStudent as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class ParentStudent extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
