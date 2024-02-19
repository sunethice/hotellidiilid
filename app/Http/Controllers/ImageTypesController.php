<?php

namespace App\Http\Controllers;

use App\Http\Repository\Contracts\IImageTypeRepository;
use App\Http\Traits\JsonResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ImageTypesController extends Controller
{
    use JsonResponseTrait;
    protected $cIImageTypeRepository;

    public function __construct(IImageTypeRepository $pIImageTypeRepository)
    {
        $this->cIImageTypeRepository = $pIImageTypeRepository;
    }

    public function cpListImageTypes(Request $request)
    {
        $mImageTypes = $this->cIImageTypeRepository->cpListImageTypes();
        if (!$mImageTypes) {
            return $this->cpFailureResponse(500, "Image types could not be listed");
        }
        return $this->cpResponseWithResults($mImageTypes, "Image types listed successfully.");
    }

    public function cpUpdateImageDescr(Request $request)
    {
        $mValidate = Validator::make($request->all(), [
            'type_code' => 'required|string',
            'lang' => 'required|string',
            'description' => 'required|string'
        ]);
        if ($mValidate->fails()) {
            return $this->cpFailureResponse(422, $mValidate->errors());
        }
        $mUpdated = $this->cIImageTypeRepository->cpUpdateImageDescr($request->all());
        if (!$mUpdated) {
            return $this->cpFailureResponse(500, "Image description could not be updated");
        }
        return $this->cpSuccessResponse("Image description updaed successfully.");
    }

    public function cpIndexModel(Request $request)
    {
        $mImageTypes = $this->cIImageTypeRepository->cpIndexModel($request["client_id"]);
        if (!$mImageTypes) {
            return $this->cpFailureResponse(500, "Image types could not be indexed");
        }
        return $this->cpSuccessResponse("Image types indexed successfully.");
    }
}
