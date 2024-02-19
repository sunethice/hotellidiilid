<?php

namespace App\Http\Repository\Contracts;

interface ICategoryRepository
{
    public function cpGetCategoryByCode($pCatCode);
    public function cpListCatByAccomType($pAccommodationType);
    public function cpListCatByCatGroup($pCatGroup);
    public function cpListCatByCatCodes($pCatCodes);
    public function cpUpdateCatAttribute($pUpdateValues);
}
