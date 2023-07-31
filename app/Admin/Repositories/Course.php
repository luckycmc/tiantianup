<?php

namespace App\Admin\Repositories;

use App\Models\Course as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class Course extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
