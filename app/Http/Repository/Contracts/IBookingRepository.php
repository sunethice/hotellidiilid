<?php

namespace App\Http\Repository\Contracts;

interface IBookingRepository
{
    public function cpGetAvalByDestination($pClientID, $pParams, $pFilters = []);
    public function cpGetAvalByHotel($pClientID, $pParams);
    public function cpGetAvalRoomsByHotel($pClientID, $pParams);
    public function cpGetAvalByGeoLocation($pClientID, $pParams, $pFilters = []);
    public function cpCheckRate($pClientID, $pParams);
    public function cpProcessBooking($pClientID, $pParams);
    public function cpGetBookingDetail($pClientID, $pParams);
    public function cpListBookings($pClientID, $pParams);
    public function cpListMyBookings($pUserEmail);
    public function cpRequestCancellation($pReference);
    public function cpCancelBooking($pClientID, $pParams);
}
