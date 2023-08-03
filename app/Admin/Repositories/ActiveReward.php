<?php

namespace App\Admin\Repositories;

use App\Models\ActiveReward as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class ActiveReward extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
