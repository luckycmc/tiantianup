<?php

namespace App\Admin\Repositories;

use App\Models\OrganRole as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class OrganRole extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
