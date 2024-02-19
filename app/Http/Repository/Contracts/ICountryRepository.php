<?php

namespace App\Http\Repository\Contracts;

interface ICountryRepository
{
    public function cpGetCountryByCode($pCountryCode);
    public function cpGetCountriesByPhrase($pCountryPhrase);
    public function cpUpdateCountryDescr($pUpdateValues);
    public function cpListStatesByCountry($pCountryCode);

    //to be removed
    public function cpIndexCountry($pClientID);
}
