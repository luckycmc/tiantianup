<?php

namespace App\Admin\Repositories;

use App\Models\ServicePrice as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class ServicePrice extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    // protected $eloquentClass = Model::class;

    public function __construct($relations = [])
    {
        $this->eloquentClass = Model::class;

        parent::__construct($relations);
    }
}
