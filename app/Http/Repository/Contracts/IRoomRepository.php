<?php

namespace App\Http\Repository\Contracts;

interface IRoomRepository
{
    public function cpGetRoomByRoomCode($pRoomCode);
    public function cpListRoomsByRoomCodes($pRoomCodes);
    public function cpUpdateRoomAttribute($pUpdateValues);
}
