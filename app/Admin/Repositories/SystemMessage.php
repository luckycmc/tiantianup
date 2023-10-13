<?php

namespace App\Admin\Repositories;

use App\Models\SystemMessage as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class SystemMessage extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
