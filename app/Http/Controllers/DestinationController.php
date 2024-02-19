<?php

namespace App\Http\Controllers;

use App\Http\Enums\RequestTypes;
use App\Http\Repository\Contracts\IDestinationRepository;
use Illuminate\Http\Request;
use App\Http\Traits\JsonResponseTrait;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class DestinationController extends Controller
{
    use JsonResponseTrait;

    protected $cIDestinationRepository;
    public function __construct(IDestinationRepository $pIDestinationRepository)
    {
        $this->cIDestinationRepository = $pIDestinationRepository;
    }

    public function cpIndexDestinations(Request $request)
    {
        $mDestinations = $this->cIDestinationRepository->cpIndexDestinations($request->user()->token()->client_id);
        if (!$mDestinations) {
            return $this->cpFailureResponse(500, "Destiantions could not be retrieved");
        }
        return $this->cpResponseWithResults($mDestinations, "Destiantions retrieved successfully.");
    }

    public function cpIndexDest(Request $request)
    {
        $mDestinations = $this->cIDestinationRepository->cpIndex();
        if (!$mDestinations) {
            return $this->cpFailureResponse(500, "Destiantions could not be retrieved");
        }
        return $this->cpResponseWithResults($mDestinations, "Destiantions retrieved successfully.");
    }

    public function cpIndexByCountryCode(Request $request)
    {
        $mValidate = Validator::make($request->all(), [
            'country_code' => 'required|string'
        ]);
        if ($mValidate->fails())
            return $this->cpFailureResponse(422, $mValidate->errors());
        $mDestinations = $this->cIDestinationRepository->cpIndexByCountryCode($request["country_code"]);
        if (!$mDestinations) {
            return $this->cpFailureResponse(500, "Destiantions could not be retrieved");
        }
        return $this->cpResponseWithResults($mDestinations, "Destiantions retrieved successfully.");
    }

    public function cpUpdateDestAttribute(Request $request)
    {
        $mAcceptedAttr = array('description', 'name');
        $mValidate = Validator::make($request->all(), [
            'destination_code' => 'required|string',
            'lang' => 'required|string',
            'attribute' => [
                'required',
                Rule::in($mAcceptedAttr)
            ],
            'value' => 'required|string'
        ]);
        if ($mValidate->fails())
            return $this->cpFailureResponse(422, $mValidate->errors());
        $mUpdated = $this->cIDestinationRepository->cpUpdateDestAttribute($request->all());
        if (!$mUpdated) {
            return $this->cpFailureResponse(500, "Destination could not be updated");
        }
        return $this->cpSuccessResponse("Destination updated successfully.");
    }

    public function cpUpdateZoneDescr(Request $request)
    {
        $mValidate = Validator::make($request->all(), [
            'destination_code' => 'required|string',
            'zone_code' => 'required|string',
            'lang' => 'required|string',
            'description' => 'required|string'
        ]);
        if ($mValidate->fails())
            return $this->cpFailureResponse(422, $mValidate->errors());
        $mUpdated = $this->cIDestinationRepository->cpUpdateZoneDescr($request->all());
        if (!$mUpdated) {
            return $this->cpFailureResponse(500, "Zone description could not be updated");
        }
        return $this->cpSuccessResponse(200, "Zone description updated successfully.");
    }

    public function cpListZonesByDest(Request $request)
    {
        $mValidate = Validator::make($request->all(), [
            'destination_code' => 'required|string'
        ]);
        if ($mValidate->fails())
            return $this->cpFailureResponse(422, $mValidate->errors());
        $mZones = $this->cIDestinationRepository->cpListZonesByDest($request["destination_code"]);
        if (is_null($mZones)) {
            return $this->cpFailureResponse(500, "Zones could not be retrieved");
        }
        return $this->cpResponseWithResults($mZones, "Zones retrieved successfully.");
    }
}
