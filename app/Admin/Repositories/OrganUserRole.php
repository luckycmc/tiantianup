<?php

namespace App\Admin\Repositories;

use App\Models\OrganUserRole as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class OrganUserRole extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
