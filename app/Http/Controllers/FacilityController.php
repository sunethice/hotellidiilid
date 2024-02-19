<?php

namespace App\Http\Controllers;

use App\Http\Repository\Contracts\IFacilityRepository;
use App\Http\Traits\JsonResponseTrait;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FacilityController extends Controller
{
    use JsonResponseTrait;
    private $cIFacilityRepository;

    public function __construct(IFacilityRepository $pIFacilityRepository)
    {
        $this->cIFacilityRepository = $pIFacilityRepository;
    }

    public function cpUpdateFacilityDescr(Request $request)
    {
        $mValidate = Validator::make($request->all(), [
            'facility_code' => 'required|string',
            'lang' => 'required|string',
            'description' => 'required|string|unique_translation:facilities'
        ]);
        if ($mValidate->fails()) {
            return $this->cpFailureResponse(422, $mValidate->errors());
        }
        try {
            if ($this->cIFacilityRepository->cpUpdateFacilityDescr($request->all())) {
                return $this->cpSuccessResponse("Facility description updated successfully");
            }
            return $this->cpFailureResponse(422, "Facility description update was unsuccessful.");
        } catch (QueryException $pEx) {
            return $this->cpFailureResponse($pEx->getCode(), $pEx->getMessage(), $pEx);
        }
    }

    public function cpGetFacilityByCode(Request $request)
    {
        $mValidate = Validator::make($request->all(), [
            'facility_code' => 'required|string'
        ]);
        if ($mValidate->fails()) {
            return $this->cpFailureResponse(422, $mValidate->errors());
        }
        try {
            $mFacility = $this->cIFacilityRepository->cpGetFacilityByCode($request["facility_code"]);
            if ($mFacility) {
                return $this->cpResponseWithResults($mFacility, "Facility retrieved successfully");
            }
            return $this->cpFailureResponse(500, "Retrieving facility was unsuccessful.");
        } catch (QueryException $pEx) {
            return $this->cpFailureResponse($pEx->getCode(), $pEx->getMessage(), $pEx);
        }
    }
    public function cpListFacilitiesByFacilityCodes(Request $request)
    {
        // Validator::extend('numericarray', function ($attribute, $value, $parameters) {
        //     if (is_array($value)) {
        //         foreach ($value as $v) {
        //             if (!is_int($v)) return false;
        //         }
        //         return true;
        //     }
        //     return is_int($value);
        // });
        $mValidate = Validator::make($request->all(), [
            'facility_codes' => 'required|array', //|numericarray'
            'facility_codes.*' => 'sometimes|integer'
        ]);

        if ($mValidate->fails()) {
            return $this->cpFailureResponse(422, $mValidate->errors());
        }
        try {
            $mFacilities = $this->cIFacilityRepository->cpListFacilitiesByFacilityCodes($request["facility_codes"]);
            if ($mFacilities) {
                return $this->cpResponseWithResults($mFacilities, "Facilities listed successfully");
            }
            return $this->cpFailureResponse(500, "Facilities Boards was unsuccessful.");
        } catch (QueryException $pEx) {
            return $this->cpFailureResponse($pEx->getCode(), $pEx->getMessage(), $pEx);
        }
    }
    public function cpGetFacilityByPhrase(Request $request)
    {
        $mValidate = Validator::make($request->all(), [
            'phrase' => 'required|string'
        ]);
        if ($mValidate->fails()) {
            return $this->cpFailureResponse(422, $mValidate->errors());
        }
        try {
            $mFacilities = $this->cIFacilityRepository->cpGetFacilityByPhrase($request["phrase"]);
            return $this->cpResponseWithResults($mFacilities, "Facilities retrieved successfully.");
        } catch (QueryException $pEx) {
            return $this->cpFailureResponse($pEx->getCode(), $pEx->getMessage(), $pEx);
        }
    }

    public function cpIndexModel(Request $request)
    {
        try {
            $mFacilities = $this->cIFacilityRepository->cpIndexModel($request->get('client_id'));
            return $this->cpResponseWithResults($mFacilities, "Facilities indexed successfully", 200);
        } catch (QueryException $pEx) {
            return $this->cpFailureResponse($pEx->getCode(), $pEx->getMessage(), $pEx);
        }
    }
}
