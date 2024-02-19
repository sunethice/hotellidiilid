<?php

namespace App\Http\Controllers;

use App\Http\Repository\Contracts\IFacilityGroupRepository;
use App\Http\Traits\JsonResponseTrait;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FacilityGroupController extends Controller
{
    use JsonResponseTrait;
    private $cIFacilityRepository;

    public function __construct(IFacilityGroupRepository $pIFacilityGroupRepository)
    {
        $this->cIFacilityGroupRepository = $pIFacilityGroupRepository;
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
            if ($this->cIFacilityGroupRepository->cpUpdateFacilityDescr($request->all())) {
                return $this->cpSuccessResponse("Facility description updated successfully");
            }
            return $this->cpFailureResponse(422, "Facility description update was unsuccessful.");
        } catch (QueryException $pEx) {
            return $this->cpFailureResponse($pEx->getCode(), $pEx->getMessage(), $pEx);
        }
    }

    public function cpIndexModel(Request $request)
    {
        try {
            $mFacilityGroups = $this->cIFacilityGroupRepository->cpIndexModel($request->get('client_id'));
            return $this->cpResponseWithResults($mFacilityGroups, "Facility groups indexed successfully", 200);
        } catch (QueryException $pEx) {
            return $this->cpFailureResponse($pEx->getCode(), $pEx->getMessage(), $pEx);
        }
    }
}
