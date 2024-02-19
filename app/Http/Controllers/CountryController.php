<?php

namespace App\Http\Controllers;

use App\Http\Repository\Contracts\ICountryRepository;
use App\Http\Traits\JsonResponseTrait;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class CountryController extends Controller
{
    use JsonResponseTrait;
    private $cICountryRepository;

    public function __construct(ICountryRepository $pICountryRepository)
    {
        $this->cICountryRepository = $pICountryRepository;
    }

    public function cpIndexCountry(Request $request)
    {
        try {
            $mCountries = $this->cICountryRepository->cpIndexCountry($request->user()->token()->client_id);
            return $this->cpResponseWithResults($mCountries, "Country indexed successfully", 200);
        } catch (QueryException $pEx) {
            return $this->cpFailureResponse($pEx->getCode(), $pEx->getMessage(), $pEx);
        }
    }

    public function cpIndexCountries(Request $request)
    {
        try {
            $mCountries = $this->cICountryRepository->cpIndex();
            return $this->cpResponseWithResults($mCountries, "Countries indexed successfully", 200);
        } catch (QueryException $pEx) {
            return $this->cpFailureResponse($pEx->getCode(), $pEx->getMessage(), $pEx);
        }
    }

    public function cpUpdateCountryDescr(Request $request)
    {
        $mValidate = Validator::make($request->all(), [
            'country_code' => 'required|string',
            'lang' => 'required|string',
            'description' => 'required|string|unique_translation:countries'
        ]);
        if ($mValidate->fails()) {
            return $this->cpFailureResponse(422, $mValidate->errors());
        }
        try {
            if ($this->cICountryRepository->cpUpdateCountryDescr($request->all())) {
                return $this->cpSuccessResponse("Country description updated successfully");
            }
            return $this->cpFailureResponse(422, "Country description update was unsuccessful.");
        } catch (QueryException $pEx) {
            return $this->cpFailureResponse($pEx->getCode(), $pEx->getMessage(), $pEx);
        }
    }

    public function cpListStatesByCountry(Request $request)
    {
        $mValidate = Validator::make($request->all(), [
            'country_code' => 'required|string'
        ]);
        if ($mValidate->fails())
            return $this->cpFailureResponse(422, $mValidate->errors());
        $mStates = $this->cICountryRepository->cpListStatesByCountry($request["country_code"]);
        if (is_null($mStates)) {
            return $this->cpFailureResponse(500, "States could not be retrieved");
        }
        return $this->cpResponseWithResults($mStates, "States retrieved successfully.");
    }
}
