<?php

namespace App\Http\Controllers;

use App\Http\Repository\Contracts\ICountryRepository;
use App\Http\Repository\Contracts\IDestinationRepository;
use App\Http\Traits\JsonResponseTrait;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class LocationController extends Controller
{
    use JsonResponseTrait;
    protected $cICountryRepository;
    protected $cIDestinationRepository;

    public function __construct(ICountryRepository $pICountryRepository, IDestinationRepository $pIDestinationRepository)
    {
        $this->cICountryRepository = $pICountryRepository;
        $this->cIDestinationRepository = $pIDestinationRepository;
    }

    public function cpListLocationsByPhrase(Request $request)
    {
        $mValidate = Validator::make($request->all(), [
            'phrase' => 'required|string'
        ]);
        if ($mValidate->fails()) {
            return $this->cpFailureResponse(422, $mValidate->errors());
        }
        try {
            $mLocations = [];
            // $mLocations["Countries"] = $this->cICountryRepository->cpGetCountriesByPhrase($request["phrase"]);
            // $mLocations["Destinations"] = $this->cIDestinationRepository->cpGetDestByPhrase($request["phrase"]);
            $mLocations = $this->cIDestinationRepository->cpGetDestByPhrase($request["phrase"]);
            return $this->cpResponseWithResults($mLocations, "Loactions retrieved successfully.");
        } catch (QueryException $pEx) {
            return $this->cpFailureResponse($pEx->getCode(), $pEx->getMessage(), $pEx);
        }
    }

    public function cpListLocationsByPhraseWC(Request $request)
    {
        $mValidate = Validator::make($request->all(), [
            'phrase' => 'required|string'
        ]);
        if ($mValidate->fails()) {
            return $this->cpFailureResponse(422, $mValidate->errors());
        }
        try {
            $mLocations = [];
            $mLocations["Destinations"] = $this->cIDestinationRepository->cpGetDestByPhraseWithCountry($request["phrase"]);
            return $this->cpResponseWithResults($mLocations, "Loactions retrieved successfully.");
        } catch (QueryException $pEx) {
            return $this->cpFailureResponse($pEx->getCode(), $pEx->getMessage(), $pEx);
        }
    }
}
