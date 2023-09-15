<?php

namespace App\Admin\Repositories;

use App\Models\Consult as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class Consult extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
