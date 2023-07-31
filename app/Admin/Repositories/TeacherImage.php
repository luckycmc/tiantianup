<?php

namespace App\Admin\Repositories;

use App\Models\TeacherImage as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class TeacherImage extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
