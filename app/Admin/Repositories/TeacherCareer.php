<?php

namespace App\Admin\Repositories;

use App\Models\TeacherCareer as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class TeacherCareer extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
