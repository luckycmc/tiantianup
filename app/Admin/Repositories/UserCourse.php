<?php

namespace App\Admin\Repositories;

use App\Models\UserCourse as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class UserCourse extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
