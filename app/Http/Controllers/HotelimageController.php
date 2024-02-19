<?php

namespace App\Http\Controllers;

use App\Http\Repository\Contracts\IHotelImageRepository;
use App\Http\Traits\JsonResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class HotelimageController extends Controller
{
    use JsonResponseTrait;
    private $cIHotelImageRepository;
    public function __construct(IHotelImageRepository $pIHotelImageRepository)
    {
        $this->cIHotelImageRepository = $pIHotelImageRepository;
    }

    public function cpAddImageToHotel(Request $request)
    {
        $mValidate = Validator::make($request->all(), [
            'type_code' => 'required|code',
            'type_description' => '',
            'order' => 'int',
            'visual_order' => 'int',
            'hotel_code' => 'required|int',
            'active' => 'boolean',
            'file' => 'image'
        ]);
        if ($mValidate->fails()) {
            return $this->cpFailureResponse(422, $mValidate->errors());
        }
        if ($request->hasFile('file')) {
            $mFile = $request->file('file');
            $mSaved = $this->cIHotelImageRepository->cpAddImageToHotel($request->all());
            if (!$mSaved) {
                return $this->cpFailureResponse(500, "Hotel image could not be uploaded.");
            }
            return $this->cpSuccessResponse("Hotel image uploaded successfully.");
        }
    }

    public function cpGetImagesByHotelID(Request $request)
    {
        $mValidate = Validator::make($request->all(), [
            'hotel_code' => 'required|int'
        ]);
        if ($mValidate->fails()) {
            return $this->cpFailureResponse(422, $mValidate->errors());
        }
        $mImages = $this->cIHotelImageRepository->cpGetImagesByHotelID($request['hotel_code']);
        if (!$mImages) {
            return $this->cpFailureResponse(500, "Images could not be retrieved.");
        }
        return $this->cpResponseWithResults($mImages, "Images retrieved successfully.");
    }

    public function cpUpdateActiveStatus(Request $request)
    {
        $mValidate = Validator::make($request->all(), [
            'id' => 'required|string',
            'status' => 'required|boolean'
        ]);
        if ($mValidate->fails()) {
            return $this->cpFailureResponse(422, $mValidate->errors());
        }
        $mUpdated = $this->cIHotelImageRepository->cpUpdateActiveStatus($request->al());
        if (!$mUpdated) {
            return $this->cpFailureResponse(500, "Hotel image status could not be updated.");
        }
        return $this->cpSuccessResponse("Hotel image status updated successfully.");
    }

    public function cpRemoveHotelImage()
    {
    }
}
