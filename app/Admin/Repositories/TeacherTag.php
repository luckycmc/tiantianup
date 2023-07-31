<?php

namespace App\Admin\Repositories;

use App\Models\TeacherTag as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class TeacherTag extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
