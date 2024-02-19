<?php

namespace App\Http\Repository\Contracts;

interface IFacilityRepository
{
    public function cpGetFacilityByCode($pFacilityCode);
    public function cpListFacilitiesByFacilityCodes($pFacilityCodes);
    public function cpGetFacilityByPhrase($pFacilityPhrase);
    public function cpUpdateFacilityDescr($pUpdateValues);
}
