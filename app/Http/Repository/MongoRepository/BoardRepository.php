<?php

namespace App\Http\Repository\MongoRepository;

use App\Http\Enums\RequestTypes;
use App\Http\Repository\Contracts\IBoardRepository;
use App\Http\Traits\HBApiTrait;
use App\Models\Board;
use Illuminate\Support\Facades\Log;

class BoardRepository extends BaseRepository implements IBoardRepository
{
    use HBApiTrait;
    public function __construct(Board $model)
    {
        parent::__construct($model);
    }

    //Need to remove
    public function cpIndexBoards($pClientID)
    {
        $mHBBoards = $this->model->get(); //->toArray();
        if (!count($mHBBoards)) {
            $mHBBoards = $this->cpSendApiRequest(
                'hotel-content-api/1.0/types/boards',
                RequestTypes::GET,
                $pClientID
            );
            $this->cpMassCreate($mHBBoards->json()["boards"]);
            return $mHBBoards->json()["boards"];
        }
        return $mHBBoards;
    }

    public function cpUpdateBoardDescr($pUpdateValues)
    {
        $mBoard = Board::where('board_code', $pUpdateValues['board_code'])->first();
        $mSaved = false;
        if (!is_null($mBoard))
            $mSaved = $mBoard->setTranslation('description', $pUpdateValues['lang'], $pUpdateValues['description'])->save();
        return $mSaved;
    }

    public function cpListBoardsByBoardCodes($pBoardCodes)
    {
        $mBoards = $this->model->whereIn('board_code', $pBoardCodes)->get();
        if (!$mBoards)
            return null;
        return $mBoards;
    }

    //Need to remove
    public function cpMassCreate(array $pDestArr)
    {
        $mUpsertArr = array_map(function ($pDest) {
            $arr = [
                "board_code" => $pDest["code"],
                "description" => $pDest["description"]['content'],
                "multilingual_code" => $pDest["multiLingualCode"]
            ];
            $mSaved = $this->model->insert($arr);
            $arr["lang"] = "en";
            $this->cpUpdateBoardDescr($arr);
            return $pDest;
        }, $pDestArr);
        return true;
    }
}
