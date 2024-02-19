<?php

namespace App\Http\Repository\MongoRepository;

use App\Http\Enums\RequestTypes;
use App\Http\Repository\Contracts\IHotelImageRepository;
use App\Http\Traits\HBApiTrait;
use App\Http\Traits\HBFileStoreTrait;
use App\Models\Hotelimage;
use Exception;
use Illuminate\Support\Facades\Log;

class HotelImageRepository extends BaseRepository implements IHotelImageRepository
{
    use HBApiTrait, HBFileStoreTrait;
    public function __construct(Hotelimage $model)
    {
        parent::__construct($model);
    }

    public function cpGetImageByID($pImageID)
    {
        $mImage = $this->model->where('id', $pImageID)->first();
        if (!$mImage) {
            return null;
        }
        return $mImage;
    }

    public function cpGetImagesByHotelID($pHotelID)
    {
        $mImages = $this->model->select('path')->where('hotel_code', $pHotelID)->get();
        if (!$mImages) {
            return null;
        }
        return $mImages;
    }

    public function cpGetImageByHotelIDOrder($pHotelID, $pOrder)
    {
        $mImage = $this->model->select('path')->where('hotel_code', $pHotelID)->where('order', $pOrder)->first();
        if (!$mImage) {
            return null;
        }
        return $mImage;
    }

    public function cpAddImageToHotel($pValues)
    {
        try {
            $mFilePath = $this->cpSaveImageToDB($pValues->file('file'));
            if (!is_null($mFilePath)) {
                $pValues['path'] = $mFilePath;
                $mSaved = $this->model->insert($pValues);
                if (!$mSaved) {
                    //should I remove the image from storage
                }
                return $mSaved;
            }
            return false;
        } catch (Exception $mEx) {
            return $mEx->getMessage();
        }
    }

    public function cpSaveImageToDB($pImage)
    {
        $pFilePath = $this->cpUploadFileToStorage($pImage, "Images");
        if (!$pFilePath) {
            return null;
        }
        return $pFilePath;
    }

    public function cpUpdateActiveStatus($pUpdateValues)
    {
        $mHotelImage = $this->cpGetImageByID($pUpdateValues['image_id']);
        $mHotelImage->active = $pUpdateValues["status"];
        $mUpdated = $mHotelImage->save();
        return $mUpdated;
    }

    public function cpIndexModel($pClientID)
    {
        $mModel = $this->model->get();
        if (!count($mModel)) {
            $mModel = $this->cpSendApiRequest(
                'hotel-content-api/1.0/hotels',
                RequestTypes::GET,
                $pClientID
            );
            $this->cpMassCreate($mModel->json()["hotels"]);
            return $mModel->json()["hotels"];
        }
        return $mModel;
    }

    //Need to remove
    public function cpMassCreate(array $pModelArr)
    {
        $mUpsertArr = array_map(function ($pModel) {
            $arr = [
                'type_code' => $pModel['imageTypeCode'],
                'path' => $pModel['type'],
                'visual_order' => $pModel['visualOrder'],
                'min_pax' => $pModel['minPax'],
                'max_pax' => $pModel['maxPax'],
                'max_adults' => $pModel['maxAdults'],
                'max_children' => $pModel['maxChildren'],
                'min_adults' => $pModel['minAdults'],
                'type_description' => array_key_exists('typeDescription', $pModel) ? $pModel["typeDescription"]["content"] : "",
                'characteristic_description' => array_key_exists('characteristicDescription', $pModel) ? $pModel["characteristicDescription"]["content"] : "",
                'description' => $pModel['description']
            ];
            $mSaved = $this->model->create($arr);
            return $pModel;
        }, $pModelArr);
        return true;
    }
}
