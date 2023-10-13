<?php

namespace App\Admin\Repositories;

use App\Models\PlatformMessage as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class PlatformMessage extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
