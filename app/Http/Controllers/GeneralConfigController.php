<?php

namespace App\Http\Controllers;

use App\Http\Repository\Contracts\IGeneralConfigRepository;
use App\Http\Traits\JsonResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class GeneralConfigController extends Controller
{
    use JsonResponseTrait;
    private $cIGeneralConfigRepository;

    public function __construct(IGeneralConfigRepository $pIGeneralConfigRepository)
    {
        $this->cIGeneralConfigRepository = $pIGeneralConfigRepository;
    }

    public function cpSetGeneralMarkUp(Request $request)
    {
        $mValidate = Validator::make($request->all(), [
            'config_type' => 'required|string',
            'configuration' => 'required|numeric',
        ]);
        if ($mValidate->fails()) {
            return $this->cpFailureResponse(422, $mValidate->errors());
        }
        $mUpdated = $this->cIGeneralConfigRepository->cpSetGeneralMarkUp($request->all(), $request->user()->token()->client_id);
        if (!$mUpdated) {
            return $this->cpFailureResponse(500, "General markup could not be updated.");
        }
        return $this->cpSuccessResponse("General markup updated successfully.");
    }
}
