<?php

namespace App\Http\Repository\Contracts;

interface IHotelImageRepository
{
    public function cpGetImagesByHotelID($pHotelID);
    public function cpGetImageByHotelIDOrder($pHotelID, $pOrder);
    public function cpAddImageToHotel($pValues);
    public function cpUpdateActiveStatus($pUpdateValues);
}
