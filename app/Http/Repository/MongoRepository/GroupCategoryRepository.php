<?php

namespace App\Http\Repository\MongoRepository;

use App\Http\Enums\RequestTypes;
use App\Http\Repository\Contracts\IGroupCategoryRepository;
use App\Http\Traits\HBApiTrait;
use App\Models\Group_category;
use Illuminate\Support\Facades\Log;

class GroupCategoryRepository extends BaseRepository implements IGroupCategoryRepository
{
    use HBApiTrait;
    public function __construct(Group_category $model)
    {
        parent::__construct($model);
    }

    public function cpGetCatGroupByCode($pCatGroup)
    {
        $mCategoryGrp = $this->model->where('group_code', $pCatGroup)->first();
        if (!$mCategoryGrp)
            return null;
        return $mCategoryGrp;
    }

    public function cpUpdateCatGroupAttribute($pUpdateValues)
    {
        $mSaved = false;
        $mCategoryGrp = $this->cpGetCatGroupByCode($pUpdateValues['group_code']);
        if ($mCategoryGrp) {
            $mSaved = $mCategoryGrp->setTranslation($pUpdateValues['attribute'], $pUpdateValues['lang'], $pUpdateValues['value'])->save();
        }
        return $mSaved;
    }

    public function cpIndexModel($pClientID)
    {
        $mModel = $this->model->get();
        if (!count($mModel)) {
            $mModel = $this->cpSendApiRequest(
                'hotel-content-api/1.0/types/groupcategories',
                RequestTypes::GET,
                $pClientID
            );
            $this->cpMassCreate($mModel->json()["groupCategories"]);
            return $mModel->json()["groupCategories"];
        }
        return $mModel;
    }

    //Need to remove
    public function cpMassCreate(array $pModelArr)
    {
        $mUpsertArr = array_map(function ($pModel) {
            $arr = [
                'group_code' => $pModel["code"],
                'order' => $pModel["order"],
                'name' => $pModel["name"]['content'],
                "description" => $pModel["description"]['content']
            ];
            $mSaved = $this->model->create($arr);
            return $pModel;
        }, $pModelArr);
        return true;
    }
}
