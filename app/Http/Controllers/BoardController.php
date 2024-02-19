<?php

namespace App\Http\Controllers;

use App\Http\Repository\Contracts\IBoardRepository;
use App\Http\Traits\JsonResponseTrait;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class BoardController extends Controller
{
    use JsonResponseTrait;
    private $cIBoardRepository;

    public function __construct(IBoardRepository $pIBoardRepository)
    {
        $this->cIBoardRepository = $pIBoardRepository;
    }

    public function cpIndexBoard(Request $request)
    {
        try {
            $mBoards = $this->cIBoardRepository->cpIndexBoards($request->get('client_id'));
            return $this->cpResponseWithResults($mBoards, "Board indexed successfully", 200);
        } catch (QueryException $pEx) {
            return $this->cpFailureResponse($pEx->getCode(), $pEx->getMessage(), $pEx);
        }
    }

    public function cpListBoardsByBoardCodes(Request $request)
    {
        $mValidate = Validator::make($request->all(), [
            'board_codes' => 'required|array'
        ]);
        if ($mValidate->fails()) {
            return $this->cpFailureResponse(422, $mValidate->errors());
        }
        try {
            $mBoards = $this->cIBoardRepository->cpListBoardsByBoardCodes($request["board_codes"]);
            if ($mBoards) {
                return $this->cpResponseWithResults($mBoards, "Boards listed successfully");
            }
            return $this->cpFailureResponse(500, "Listing Boards was unsuccessful.");
        } catch (QueryException $pEx) {
            return $this->cpFailureResponse($pEx->getCode(), $pEx->getMessage(), $pEx);
        }
    }

    public function cpUpdateBoardDescr(Request $request)
    {
        $mValidate = Validator::make($request->all(), [
            'board_code' => 'required|string',
            'lang' => 'required|string',
            'description' => 'required|string|unique_translation:boards'
        ]);
        if ($mValidate->fails()) {
            return $this->cpFailureResponse(422, $mValidate->errors());
        }
        try {
            if ($this->cIBoardRepository->cpUpdateBoardDescr($request->all())) {
                return $this->cpSuccessResponse("Board description updated successfully");
            }
            return $this->cpFailureResponse(422, "Board description update was unsuccessful.");
        } catch (QueryException $pEx) {
            return $this->cpFailureResponse($pEx->getCode(), $pEx->getMessage(), $pEx);
        }
    }
}
