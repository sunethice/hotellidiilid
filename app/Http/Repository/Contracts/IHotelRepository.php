<?php

namespace App\Http\Repository\Contracts;

interface IHotelRepository
{
    public function cpGetHotelByCode($pHotelCode);
    public function cpGetHotelByName($pHotelName);
    public function cpUpdateHotelAttribute($pUpdateValues);
    public function cpUpdateActiveStatus($pUpdateValues);
    public function cpGetFacilitiesByHotelCode($pHotelCode);
    public function cpListHotelsByCountryCode($pCountryCode);
    public function cpListHotelCodesByCountry($pCountryCode, $pClientID);
    public function cpListHotelsByZone($pCountryCode, $pDestCode, $pZoneCode, $pClientID);
    public function cpListHotelCodesByDestination($pDestinationCode, $pClientID);
}
