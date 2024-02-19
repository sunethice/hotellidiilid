<?php

namespace App\Http\Controllers;

use App\Http\Repository\Contracts\IHotelRepository;
use App\Http\Traits\JsonResponseTrait;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class HotelController extends Controller
{
    use JsonResponseTrait;
    private $cIHotelRepository;
    public function __construct(IHotelRepository $pIHotelRepository)
    {
        $this->cIHotelRepository = $pIHotelRepository;
    }

    public function cpIndexModel(Request $request)
    {
        try {
            $mHotels = $this->cIHotelRepository->cpIndexModel($request->get('client_id'));
            return $this->cpResponseWithResults($mHotels, "Hotels indexed successfully", 200);
        } catch (QueryException $pEx) {
            return $this->cpFailureResponse($pEx->getCode(), $pEx->getMessage(), $pEx);
        }
    }

    public function cpGetHotelByCode(Request $request)
    {
        $mValidate = Validator::make($request->all(), [
            'hotel_code' => 'required|string'
        ]);
        if ($mValidate->fails()) {
            return $this->cpFailureResponse(422, $mValidate->errors());
        }
        try {
            $mHotels = $this->cIHotelRepository->cpGetHotelByCode($request['hotel_code']);
            if (!$mHotels)
                return $this->cpFailureResponse(500, "Hotel could not be retrieved.");
            return $this->cpResponseWithResults($mHotels, "Hotel retrieved successfully", 200);
        } catch (QueryException $pEx) {
            return $this->cpFailureResponse($pEx->getCode(), $pEx->getMessage(), $pEx);
        }
    }

    public function cpGetHotelByName(Request $request)
    {
        $mValidate = Validator::make($request->all(), [
            'name' => 'required|string'
        ]);
        if ($mValidate->fails()) {
            return $this->cpFailureResponse(422, $mValidate->errors());
        }
        try {
            $mHotels = $this->cIHotelRepository->cpGetHotelByName($request['name']);
            if (!$mHotels)
                return $this->cpFailureResponse(500, "Hotel could not be retrieved.");
            return $this->cpResponseWithResults($mHotels, "Hotel retrieved successfully", 200);
        } catch (QueryException $pEx) {
            return $this->cpFailureResponse($pEx->getCode(), $pEx->getMessage(), $pEx);
        }
    }

    public function cpUpdateHotelAttribute(Request $request)
    {
        $mAcceptedAttr = ['name', 'description'];
        $mValidate = Validator::make($request->all(), [
            'hotel_code' => 'required|string',
            'lang' => 'required|string',
            'attribute' => [
                'required',
                Rule::in($mAcceptedAttr)
            ],
            'value' => 'required|string'
        ]);
        if ($mValidate->fails()) {
            return $this->cpFailureResponse(422, $mValidate->errors());
        }
        try {
            $mUpdated = $this->cIHotelRepository->cpUpdateHotelAttribute($request->all());
            if (!$mUpdated) {
                return $this->cpFailureResponse(500, "Hotel could not be updated");
            }
            return $this->cpSuccessResponse("Hotel updated successfully.");
        } catch (QueryException $pEx) {
            return $this->cpFailureResponse($pEx->getCode(), $pEx->getMessage(), $pEx);
        }
    }

    public function cpUpdateActiveStatus(Request $request)
    {
        $mValidate = Validator::make($request->all(), [
            'status' => 'required|boolean',
        ]);
        if ($mValidate->fails()) {
            return $this->cpFailureResponse(422, $mValidate->errors());
        }
        try {
            $mUpdated = $this->cIHotelRepository->cpUpdateActiveStatus($request["status"]);
            if (!$mUpdated) {
                return $this->cpFailureResponse(500, "Hotel could not be updated");
            }
            return $this->cpSuccessResponse("Hotel updated successfully.");
        } catch (QueryException $pEx) {
            return $this->cpFailureResponse($pEx->getCode(), $pEx->getMessage(), $pEx);
        }
    }

    public function cpGetFacilitiesByHotelCode(Request $request)
    {
        $mValidate = Validator::make($request->all(), [
            'hotel_code' => 'required|string'
        ]);
        if ($mValidate->fails()) {
            return $this->cpFailureResponse(422, $mValidate->errors());
        }
        try {
            $mHotelFacilities = $this->cIHotelRepository->cpGetFacilitiesByHotelCode($request['hotel_code']);
            return $this->cpResponseWithResults($mHotelFacilities, "Hotel facilities etrieved successfully", 200);
        } catch (QueryException $pEx) {
            return $this->cpFailureResponse($pEx->getCode(), $pEx->getMessage(), $pEx);
        }
    }

    public function cpListHotelsByCountryCode(Request $request)
    {
        $mValidate = Validator::make($request->all(), [
            'country_code' => 'required|string'
        ]);
        if ($mValidate->fails()) {
            return $this->cpFailureResponse(422, $mValidate->errors());
        }
        try {
            $mHotels = $this->cIHotelRepository->cpListHotelsByCountryCode($request['country_code']);
            return $this->cpResponseWithResults($mHotels, "Hotels retrieved successfully", 200);
        } catch (QueryException $pEx) {
            return $this->cpFailureResponse($pEx->getCode(), $pEx->getMessage(), $pEx);
        }
    }

    public function cpListHotelsByZone(Request $request){
        $mValidate = Validator::make($request->all(), [
            'country_code' => 'required|string',
            'destination_code' => 'required|string',
            'zone_code' => 'required|string',
        ]);
        if ($mValidate->fails()) {
            return $this->cpFailureResponse(422, $mValidate->errors());
        }
        try {
            $mHotels = $this->cIHotelRepository->cpListHotelsByZone($request['country_code'],$request['destination_code'],$request['zone_code'], $request->get('client_id'));
            return $this->cpResponseWithResults($mHotels, "Hotels retrieved successfully");
        } catch (QueryException $pEx) {
            return $this->cpFailureResponse($pEx->getCode(), $pEx->getMessage(), $pEx);
        }
    }

    //need to check if values hould be validated before updating
    public function cpUpdateHotelDetails($pUpdateValues)
    {
        $mHotels = $this->cpGetHotelByCode($pUpdateValues["hotel_code"]);
        if (!$mHotels) {
            return false;
        }
        $mUpdated = $mHotels->update($pUpdateValues);
        return $mHotels;
    }
}
