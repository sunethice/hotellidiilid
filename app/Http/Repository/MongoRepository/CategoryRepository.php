<?php

namespace App\Http\Repository\MongoRepository;

use App\Http\Enums\RequestTypes;
use App\Http\Repository\Contracts\ICategoryRepository;
use App\Http\Traits\HBApiTrait;
use App\Models\Category;
use Illuminate\Support\Facades\Log;

class CategoryRepository extends BaseRepository implements ICategoryRepository
{
    use HBApiTrait;
    protected $model;
    public function __construct(Category $model)
    {
        $this->model = $model;
    }

    public function cpGetCategoryByCode($pCatCode)
    {
        $mCategory = $this->model->where('category_code', $pCatCode)->first();
        if (!$mCategory)
            return null;
        return $mCategory;
    }

    public function cpListCatByAccomType($pAccommodationType)
    {
        $mCategoryList = $this->model->where('accommodation_type', strtoupper($pAccommodationType))->get();
        if (!$mCategoryList)
            return [];
        return $mCategoryList;
    }

    public function cpListCatByCatCodes($pCatCodes)
    {
        $mCategoryList = $this->model->whereIn('category_code', $pCatCodes)->get();
        if (!$mCategoryList)
            return null;
        return $mCategoryList;
    }

    public function cpListCatByCatGroup($pCatGroup)
    {
        $mCategoryList = $this->model->where("group", $pCatGroup)->get();
        if (!$mCategoryList)
            return [];
        return $mCategoryList;
    }

    public function cpUpdateCatAttribute($pUpdateValues)
    {
        $mSaved = false;
        $mCategory = $this->cpGetCategoryByCode($pUpdateValues['category_code']);
        if ($mCategory) {
            $mSaved = $mCategory->setTranslation($pUpdateValues['attribute'], $pUpdateValues['lang'], $pUpdateValues['value'])->save();
        }
        return $mSaved;
    }

    public function cpIndexModel($pClientID)
    {
        $mModel = $this->model->get();
        if (!count($mModel)) {
            $mModel = $this->cpSendApiRequest(
                'hotel-content-api/1.0/types/categories',
                RequestTypes::GET,
                $pClientID
            );
            $this->cpMassCreate($mModel->json()["categories"]);
            return $mModel->json()["categories"];
        }
        return $mModel;
    }

    //Need to remove
    public function cpMassCreate(array $pModelArr)
    {
        $mUpsertArr = array_map(function ($pModel) {
            $arr = [
                "category_code" => $pModel["code"],
                'simple_code' => $pModel["simpleCode"],
                'accommodation_type' => $pModel["accommodationType"],
                'group' => array_key_exists('group', $pModel) ? $pModel["group"] : "",
                "description" => array_key_exists('description', $pModel) ? $pModel["description"]['content'] : "",
            ];
            $mSaved = $this->model->create($arr);
            return $pModel;
        }, $pModelArr);
        return true;
    }
}
