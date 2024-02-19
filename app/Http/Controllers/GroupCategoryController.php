<?php

namespace App\Http\Controllers;

use App\Http\Repository\Contracts\IGroupCategoryRepository;
use App\Http\Traits\JsonResponseTrait;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class GroupCategoryController extends Controller
{
    use JsonResponseTrait;
    protected $cIGroupCategoryRepository;
    public function __construct(IGroupCategoryRepository $pIGroupCategoryRepository)
    {
        $this->cIGroupCategoryRepository = $pIGroupCategoryRepository;
    }

    public function cpIndexModel(Request $request)
    {
        try {
            $mGrpCat = $this->cIGroupCategoryRepository->cpIndexModel($request->get('client_id'));
            return response()->json($mGrpCat);
        } catch (QueryException $pEx) {
            return $this->cpFailureResponse($pEx->getCode(), $pEx->getMessage(), $pEx);
        }
    }

    public function cpUpdateCatGroupAttribute(Request $request)
    {
        $mAcceptedAttr = array('name', 'description');
        $mValidate = Validator::make($request->all(), [
            'group_code' => 'required|string',
            'lang' => 'required|string',
            'attribute' => [
                'required',
                Rule::in($mAcceptedAttr)
            ],
            'value' => 'required|string|unique_translation:group_categories'
        ]);
        if ($mValidate->fails()) {
            return $this->cpFailureResponse(422, $mValidate->errors());
        }
        try {
            if ($this->cIGroupCategoryRepository->cpUpdateCatGroupAttribute($request->all())) {
                return $this->cpSuccessResponse("Group category updated successfully");
            }
            return $this->cpFailureResponse(422, "Group category update was unsuccessful.");
        } catch (QueryException $pEx) {
            return $this->cpFailureResponse($pEx->getCode(), $pEx->getMessage(), $pEx);
        }
    }
}
