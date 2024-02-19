<?php

namespace App\Http\Controllers;

use App\Http\Repository\Contracts\IRoomRepository;
use App\Http\Traits\JsonResponseTrait;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class RoomController extends Controller
{
    use JsonResponseTrait;
    protected $cICategoryRepository;
    public function __construct(IRoomRepository $pIRoomRepository)
    {
        $this->cIRoomRepository = $pIRoomRepository;
    }

    public function cpIndexModel(Request $request)
    {
        try {
            $mRooms = $this->cIRoomRepository->cpIndexModel($request->get('client_id'));
            return response()->json($mRooms);
        } catch (QueryException $pEx) {
            return $this->cpFailureResponse($pEx->getCode(), $pEx->getMessage(), $pEx);
        }
    }

    public function cpUpdateRoomAttribute(Request $request)
    {
        $mAcceptedAttr = array('characteristic_description', 'type_description', 'description');
        $mValidate = Validator::make($request->all(), [
            'room_code' => 'required|string',
            'lang' => 'required|string',
            'attribute' => [
                'required',
                Rule::in($mAcceptedAttr)
            ],
            'value' => 'required|string|unique_translation:rooms'
        ]);
        if ($mValidate->fails()) {
            return $this->cpFailureResponse(422, $mValidate->errors());
        }
        try {
            if ($this->cIRoomRepository->cpUpdateRoomAttribute($request->all())) {
                return $this->cpSuccessResponse("Room updated successfully");
            }
            return $this->cpFailureResponse(422, "Room update was unsuccessful.");
        } catch (QueryException $pEx) {
            return $this->cpFailureResponse($pEx->getCode(), $pEx->getMessage(), $pEx);
        }
    }

    public function cpListRoomsByRoomCodes(Request $request)
    {
        $mValidate = Validator::make($request->all(), [
            'room_codes' => 'required|array'
        ]);
        if ($mValidate->fails()) {
            return $this->cpFailureResponse(422, $mValidate->errors());
        }
        try {
            $mRooms = $this->cIRoomRepository->cpListRoomsByRoomCodes($request["room_codes"]);
            if ($mRooms) {
                return $this->cpResponseWithResults($mRooms, "Rooms listed successfully");
            }
            return $this->cpFailureResponse(500, "Listing Rooms was unsuccessful.");
        } catch (QueryException $pEx) {
            return $this->cpFailureResponse($pEx->getCode(), $pEx->getMessage(), $pEx);
        }
    }
}
