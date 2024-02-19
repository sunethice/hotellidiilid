<?php

namespace App\Http\Repository\Contracts;

interface IImageTypeRepository
{
    public function cpListImageTypes();
    public function cpUpdateImageDescr($pUpdateValues);
}
