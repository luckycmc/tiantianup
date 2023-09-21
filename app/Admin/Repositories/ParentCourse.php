<?php

namespace App\Admin\Repositories;

use App\Models\ParentCourse as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class ParentCourse extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
