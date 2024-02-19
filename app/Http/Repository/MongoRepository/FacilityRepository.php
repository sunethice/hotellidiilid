<?php

namespace App\Http\Repository\MongoRepository;

use App\Http\Enums\RequestTypes;
use App\Http\Repository\Contracts\IFacilityRepository;
use App\Http\Traits\HBApiTrait;
use App\Models\Facility;
use Illuminate\Support\Facades\Log;

class FacilityRepository extends BaseRepository implements IFacilityRepository
{
    use HBApiTrait;
    protected $model;
    public function __construct(Facility $model)
    {
        parent::__construct($model);
    }

    public function cpGetFacilityByCode($pFacilityCode)
    {
        $mFacility = $this->model->where('facility_code', $pFacilityCode['facility_code'])->first();
        if (!$mFacility)
            return null;
        return $mFacility;
    }

    public function cpListFacilitiesByFacilityCodes($pFacilityCodes)
    {
        $mFacilities = $this->model->whereIn('facility_code', array_map('intval', $pFacilityCodes))->get();
        if (!$mFacilities)
            return null;
        return $mFacilities;
    }

    public function cpGetFacilityByPhrase($pFacilityPhrase)
    {
        $mLocale = app()->getLocale();
        $mFacility = $this->model
            ->where("description", 'like', "%\"$mLocale\":\"%$pFacilityPhrase%")->get();
        if (!$mFacility)
            return null;
        return $mFacility;
    }

    public function cpUpdateFacilityDescr($pUpdateValues)
    {
        $mFacility = $this->cpGetFacilityByCode($pUpdateValues['facility_code']);
        $mSaved = $mFacility->setTranslation('description', $pUpdateValues['lang'], $pUpdateValues['description'])->save();
        return $mSaved;
    }

    public function cpIndexModel($pClientID)
    {
        $mModel = $this->model->get();
        if (!count($mModel)) {
            $mModel = $this->cpSendApiRequest(
                'hotel-content-api/1.0/types/facilities',
                RequestTypes::GET,
                $pClientID
            );
            $this->cpMassCreate($mModel->json()["facilities"]);
            return $mModel->json()["facilities"];
        }
        return $mModel;
    }

    //Need to remove
    public function cpMassCreate(array $pModelArr)
    {
        $mUpsertArr = array_map(function ($pModel) {
            $arr = [
                "facility_code" => $pModel["code"],
                'facility_group_code' => $pModel["facilityGroupCode"],
                "description" => array_key_exists('description', $pModel) ? $pModel["description"]['content'] : "",
            ];
            $mSaved = $this->model->create($arr);
            return $pModel;
        }, $pModelArr);
        return true;
    }
}
