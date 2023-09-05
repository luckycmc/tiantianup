<?php

namespace App\Admin\Repositories;

use App\Models\OrganPrivilege as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class OrganPrivilege extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
