<?php

namespace App\Admin\Repositories;

use App\Models\Education as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class Education extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
