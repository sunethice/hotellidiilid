<?php

namespace App\Http\Repository\MongoRepository;

use App\Http\Enums\RequestTypes;
use App\Http\Repository\Contracts\IFacilityGroupRepository;
use App\Http\Traits\HBApiTrait;
use App\Models\Facility_group;
use Illuminate\Support\Facades\Log;

class FacilityGroupRepository extends BaseRepository implements IFacilityGroupRepository
{
    use HBApiTrait;
    protected $model;
    public function __construct(Facility_group $model)
    {
        parent::__construct($model);
    }

    public function cpUpdateDescrByLang($pUpdateValues)
    {
        $mFacilityGrp = $this->model->where('facility_group_code', $pUpdateValues['facility_group_code'])->first();
        $mSaved = $mFacilityGrp->setTranslation('description', $pUpdateValues['lang'], $pUpdateValues['description'])->save();
        return $mSaved;
    }

    public function cpIndexModel($pClientID)
    {
        $mModel = $this->model->get();
        if (!count($mModel)) {
            $mModel = $this->cpSendApiRequest(
                'hotel-content-api/1.0/types/facilitygroups',
                RequestTypes::GET,
                $pClientID
            );
            $this->cpMassCreate($mModel->json()["facilityGroups"]);
            return $mModel->json()["facilityGroups"];
        }
        return $mModel;
    }

    //Need to remove
    public function cpMassCreate(array $pModelArr)
    {
        $mUpsertArr = array_map(function ($pModel) {
            $arr = [
                "facility_group_code" => $pModel["code"],
                "description" => array_key_exists('description', $pModel) ? $pModel["description"]['content'] : "",
            ];
            $mSaved = $this->model->create($arr);
            return $pModel;
        }, $pModelArr);
        return true;
    }
}
