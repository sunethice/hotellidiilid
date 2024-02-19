<?php

namespace App\Http\Controllers;

use App\Http\Repository\Contracts\ICategoryRepository;
use App\Http\Repository\Contracts\ICountryRepository;
use App\Http\Traits\JsonResponseTrait;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    use JsonResponseTrait;
    protected $cICategoryRepository;
    public function __construct(ICategoryRepository $pICategoryRepository)
    {
        $this->cICategoryRepository = $pICategoryRepository;
    }

    public function cpIndexModel(Request $request)
    {
        try {
            $mCategories = $this->cICategoryRepository->cpIndexModel($request->get('client_id'));
            return response()->json($mCategories);
        } catch (QueryException $pEx) {
            return $this->cpFailureResponse($pEx->getCode(), $pEx->getMessage(), $pEx);
        }
    }

    // public function cpListCatByAccomType(Request $request)
    // {
    //     $mValidate = Validator::make($request->all(), [
    //         'accommodation_type' => 'required|string'
    //     ]);
    //     if ($mValidate->fails()) {
    //         return $this->cpFailureResponse(422, $mValidate->errors());
    //     }
    //     try {
    //         if ($this->cICategoryRepository->cpListCatByAccomType($request["accommodation_type"])) {
    //             return $this->cpSuccessResponse("Categories listed successfully");
    //         }
    //         return $this->cpFailureResponse(422, "Listing categories was unsuccessful.");
    //     } catch (QueryException $pEx) {
    //         return $this->cpFailureResponse($pEx->getCode(), $pEx->getMessage(), $pEx);
    //     }
    // }

    public function cpListCatByCatCodes(Request $request)
    {
        $mValidate = Validator::make($request->all(), [
            'category_code' => 'required|array'
        ]);
        if ($mValidate->fails()) {
            return $this->cpFailureResponse(422, $mValidate->errors());
        }
        try {
            $mRooms = $this->cICategoryRepository->cpListCatByCatCodes($request["category_code"]);
            if ($mRooms) {
                return $this->cpResponseWithResults($mRooms, "Categories listed successfully");
            }
            return $this->cpFailureResponse(500, "Listing Categories was unsuccessful.");
        } catch (QueryException $pEx) {
            return $this->cpFailureResponse($pEx->getCode(), $pEx->getMessage(), $pEx);
        }
    }

    public function cpListCatByCatGroup(Request $request)
    {
        $mValidate = Validator::make($request->all(), [
            'group' => 'required|string'
        ]);
        if ($mValidate->fails()) {
            return $this->cpFailureResponse(422, $mValidate->errors());
        }
        try {
            $mCategories = $this->cICategoryRepository->cpListCatByCatGroup($request["group"]);
            if ($mCategories) {
                return $this->cpResponseWithResults($mCategories, "Categories listed successfully");
            }
            return $this->cpFailureResponse(500, "Listing categories was unsuccessful.");
        } catch (QueryException $pEx) {
            return $this->cpFailureResponse($pEx->getCode(), $pEx->getMessage(), $pEx);
        }
    }
    public function cpUpdateCatAttribute(Request $request)
    {
        $mAcceptedAttr = array('accommodation_type', 'description');
        $mValidate = Validator::make($request->all(), [
            'category_code' => 'required|string',
            'lang' => 'required|string',
            'attribute' => [
                'required',
                Rule::in($mAcceptedAttr)
            ],
            'value' => 'required|string|unique_translation:categories'
        ]);
        if ($mValidate->fails()) {
            return $this->cpFailureResponse(422, $mValidate->errors());
        }
        try {
            if ($this->cICategoryRepository->cpUpdateCatAttribute($request->all())) {
                return $this->cpSuccessResponse("Category updated successfully");
            }
            return $this->cpFailureResponse(422, "Category update was unsuccessful.");
        } catch (QueryException $pEx) {
            return $this->cpFailureResponse($pEx->getCode(), $pEx->getMessage(), $pEx);
        }
    }
}
