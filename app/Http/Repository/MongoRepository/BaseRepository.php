<?php

namespace App\Http\Repository\MongoRepository;

use App\Http\Repository\Contracts\IEloquentRepository;
use Jenssegers\Mongodb\Collection;
use Jenssegers\Mongodb\Eloquent\Model;

class BaseRepository implements IEloquentRepository
{
    protected $model;
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function cpCreate(array $pAttributes): Model
    {
        return $this->model->create($pAttributes);
    }

    public function cpIndex()
    {
        return $this->model->all();
    }
}
