<?php

namespace App\Admin\Repositories;

use App\Models\RotateImage as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class RotateImage extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
