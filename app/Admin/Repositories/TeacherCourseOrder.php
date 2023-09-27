<?php

namespace App\Admin\Repositories;

use App\Models\TeacherCourseOrder as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class TeacherCourseOrder extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
