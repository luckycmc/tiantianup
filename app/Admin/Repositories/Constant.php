<?php

namespace App\Admin\Repositories;

use App\Models\Constant as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class Constant extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
