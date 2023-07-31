<?php

namespace App\Admin\Repositories;

use App\Models\CourseImage as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class CourseImage extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
