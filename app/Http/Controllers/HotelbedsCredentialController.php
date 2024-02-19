<?php

namespace App\Http\Controllers;

use App\Http\Repository\Contracts\IHotelbedsCredentialsRepository;
use App\Http\Traits\JsonResponseTrait;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class HotelbedsCredentialController extends Controller
{
    use JsonResponseTrait;
    protected $cIHotelbedsCredentialsRepository;
    public function __construct(IHotelbedsCredentialsRepository $pIHotelbedsCredentialsRepository)
    {
        $this->cIHotelbedsCredentialsRepository = $pIHotelbedsCredentialsRepository;
    }

    public function cpStoreHBApiKey(Request $request)
    {
        $mValidate = Validator::make($request->all(), [
            'hb_api_key' => 'required|string',
            'hb_api_secret' => 'required|string'
        ]);
        if ($mValidate->fails()) {
            return $this->cpFailureResponse(422, $mValidate->errors());
        }
        try {
            $mSaved = $this->cIHotelbedsCredentialsRepository->cpAddCredentials([
                'client_id' => $request->get('client_id'),
                'hb_api_key' => $request['hb_api_key'],
                'hb_api_secret' => $request['hb_api_secret']
            ]);
            if ($mSaved) {
                return $this->cpSuccessResponse("HB keys saved successfully");
            }
            return $this->cpFailureResponse(500, "HB keys saving was unsuccessfull");
        } catch (QueryException $pEx) {
            return $this->cpFailureResponse($pEx->getCode(), $pEx->getMessage(), $pEx);
        }
    }
}
