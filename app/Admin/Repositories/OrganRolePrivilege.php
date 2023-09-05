<?php

namespace App\Admin\Repositories;

use App\Models\OrganRolePrivilege as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class OrganRolePrivilege extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
