<?php

namespace App\Admin\Repositories;

use App\Models\CourseSetting as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class CourseSetting extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
