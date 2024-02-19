<?php

namespace App\Http\Controllers;

use App\Http\Repository\Contracts\IMarkupRepository;
use App\Http\Traits\JsonResponseTrait;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class MarkupController extends Controller
{
    use JsonResponseTrait;
    private $cMarkupRepository;

    public function __construct(IMarkupRepository $pIMarkupRepository)
    {
        $this->cMarkupRepository = $pIMarkupRepository;
    }

    public function cpIndexMarkup(Request $request)
    {
        try {
            $mMarkups = $this->cMarkupRepository->cpIndex();
            return $this->cpResponseWithResults($mMarkups, "Markup indexed successfully", 200);
        } catch (QueryException $pEx) {
            return $this->cpFailureResponse($pEx->getCode(), $pEx->getMessage(), $pEx);
        }
    }

    public function cpCreateMarkup(Request $request)
    {
        $mValidate = Validator::make($request->all(), [
            'country' => 'required|string|exists:countries,country_code', //accepts country_code
            'city' => 'string|exists:destinations,destination_code', //accepts destination_code
            'from' => 'required|date',
            'to' => 'required|date|after:from',
            'sunday_pct' => 'nullable|min:1|max:99.99|regex:/^\d+(\.\d{1,2})?$/',
            'monday_pct' => 'nullable|min:1|max:99.99|regex:/^\d+(\.\d{1,2})?$/',
            'tuesday_pct' => 'nullable|min:1|max:99.99|regex:/^\d+(\.\d{1,2})?$/',
            'wednesday_pct' => 'nullable|min:1|max:99.99|regex:/^\d+(\.\d{1,2})?$/',
            'thursday_pct' => 'nullable|min:1|max:99.99|regex:/^\d+(\.\d{1,2})?$/',
            'friday_pct' => 'nullable|min:1|max:99.99|regex:/^\d+(\.\d{1,2})?$/',
            'saturday_pct' => 'nullable|min:1|max:99.99|regex:/^\d+(\.\d{1,2})?$/',
            'overwite_zero' => 'required|boolean'
        ]);
        if ($mValidate->fails()) {
            return $this->cpFailureResponse(422, $mValidate->errors());
        }
        try {
            $mMarkupCreated = $this->cMarkupRepository->cpCreateMarkup($request->all());
            if ($mMarkupCreated) {
                return $this->cpSuccessResponse("Markup created successfully");
            }
            return $this->cpSuccessResponse("Markup creation was unsuccessfull");
        } catch (QueryException $pEx) {
            return $this->cpFailureResponse($pEx->getCode(), $pEx->getMessage(), $pEx);
        }
    }

    public function cpUpdateMarkup(Request $request)
    {
        $mValidate = Validator::make($request->all(), [
            'country' => 'required|string',
            'city' => 'nullable|string',
            'stay_date' => 'required|date',
            "markup_pct" => 'nullable|min:1|max:99.99|regex:/^\d+(\.\d{1,2})?$/'
        ]);

        if ($mValidate->fails()) {
            return $this->cpFailureResponse(422, $mValidate->errors());
        }
        try {
            $mMarkupUpdated = $this->cMarkupRepository->cpUpdateMarkup($request->all());
            return $this->cpResponseWithResults($mMarkupUpdated, "Markup updated successfully", 200);
        } catch (QueryException $pEx) {
            return $this->cpFailureResponse($pEx->getCode(), $pEx->getMessage(), $pEx);
        }
    }

    public function cpSearchMarkup(Request $request)
    {
        $mValidate = Validator::make($request->all(), [
            'country' => 'required|string',
            'city' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date'
        ]);
        if ($mValidate->fails()) {
            return $this->cpFailureResponse(422, $mValidate->errors());
        }
        try {
            $mMarkupUpdated = $this->cMarkupRepository->cpAdvSearchMarkups(
                $request["start_date"],
                $request["end_date"],
                $request["country"],
                $request["city"]
            );
            return $this->cpResponseWithResults($mMarkupUpdated, "Search results fetched successfully", 200);
        } catch (QueryException $pEx) {
            return $this->cpFailureResponse($pEx->getCode(), $pEx->getMessage(), $pEx);
        }
    }
}
