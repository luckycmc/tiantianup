<?php

namespace App\Admin\Repositories;

use App\Models\OrganImage as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class OrganImage extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
