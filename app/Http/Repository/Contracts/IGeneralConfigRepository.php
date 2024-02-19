<?php

namespace App\Http\Repository\Contracts;

interface IGeneralConfigRepository
{
    public function cpSetGeneralMarkup($pUpdateValues, $pClientID);
}
