<?php

namespace App\Admin\Repositories;

use App\Models\TrianingType as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class TrianingType extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
