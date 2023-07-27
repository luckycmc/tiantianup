<?php

namespace App\Admin\Repositories;

use App\Models\UserContact as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class UserContact extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
