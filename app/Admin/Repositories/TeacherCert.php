<?php

namespace App\Admin\Repositories;

use App\Models\TeacherCert as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class TeacherCert extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
