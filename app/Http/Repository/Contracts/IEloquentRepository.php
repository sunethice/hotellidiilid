<?php

namespace App\Http\Repository\Contracts;

use Jenssegers\Mongodb\Collection;
use Jenssegers\Mongodb\Eloquent\Model;

interface IEloquentRepository
{
    public function cpCreate(array $pAttributes): Model;
    public function cpIndex();
    // public function cpSearchByID($pID): ?Model;
}
