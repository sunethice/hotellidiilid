<?php

namespace App\Http\Repository\MongoRepository;

use App\Http\Enums\RequestTypes;
use App\Http\Repository\Contracts\IImageTypeRepository;
use App\Http\Traits\HBApiTrait;
use App\Http\Traits\HBFileStoreTrait;
use App\Models\ImageTypes;

class ImageTypeRepository extends BaseRepository implements IImageTypeRepository
{
    use HBApiTrait, HBFileStoreTrait;
    public function __construct(ImageTypes $model)
    {
        parent::__construct($model);
    }

    public function cpGetImageTypeByCode($pImageTypeCode)
    {
        $mImageType = $this->model->where('code', $pImageTypeCode)->get();
        if (!$mImageType) {
            return null;
        }
        return $mImageType;
    }

    public function cpListImageTypes()
    {
        $mImageTypes = $this->model->get();
        if (!$mImageTypes)
            return null;
        return $mImageTypes;
    }

    public function cpUpdateImageDescr($pUpdateValues)
    {
        $mSaved = false;
        $mImageType = $this->cpGetImageTypeByCode($pUpdateValues['facility_code']);
        if (!is_null($mImageType)) {
            $mSaved = $mImageType->setTranslation('description', $pUpdateValues['lang'], $pUpdateValues['description'])->save();
        }
        return $mSaved;
    }

    public function cpIndexModel($pClientID)
    {
        $mModel = $this->model->get();
        if (!count($mModel)) {
            $mModel = $this->cpSendApiRequest(
                'hotel-content-api/1.0/types/imagetypes/',
                RequestTypes::GET,
                $pClientID
            );
            $this->cpMassCreate($mModel->json()["imageTypes"]);
            return $mModel->json()["imageTypes"];
        }
        return $mModel;
    }

    //Need to remove
    public function cpMassCreate(array $pModelArr)
    {
        $mUpsertArr = array_map(function ($pModel) {
            $arr = [
                'code' => $pModel['code'],
                'description' => $pModel['description']["content"]
            ];
            $mSaved = $this->model->create($arr);
            return $pModel;
        }, $pModelArr);
        return true;
    }
}
