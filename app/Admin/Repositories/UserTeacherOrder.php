<?php

namespace App\Admin\Repositories;

use App\Models\UserTeacherOrder as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class UserTeacherOrder extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
