<?php

namespace App\Http\Repository\Contracts;

interface IBoardRepository
{
    public function cpIndexBoards($pClientID);
    public function cpListBoardsByBoardCodes($pBoardCodes);
    public function cpUpdateBoardDescr($pUpdateValues);
}
