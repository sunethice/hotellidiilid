<?php

namespace App\Http\Repository\MongoRepository;

use App\Http\Enums\RequestTypes;
use App\Http\Repository\Contracts\IBoardRepository;
use App\Http\Repository\Contracts\IRoomRepository;
use App\Http\Traits\HBApiTrait;
use App\Models\Room;
use Illuminate\Support\Facades\Log;

class RoomRepository extends BaseRepository implements IRoomRepository
{
    use HBApiTrait;
    public function __construct(Room $model)
    {
        parent::__construct($model);
    }
    public function cpGetRoomByRoomCode($pRoomCode)
    {
        $mRoom = $this->model->where('room_code', $pRoomCode)->first();
        if (!$mRoom)
            return null;
        return $mRoom;
    }
    public function cpListRoomsByRoomCodes($pRoomCodes)
    {
        $mRooms = $this->model->whereIn('room_code', $pRoomCodes)->get();
        if (!$mRooms)
            return null;
        return $mRooms;
    }

    public function cpUpdateRoomAttribute($pUpdateValues)
    {
        $mSaved = false;
        $mRoom = $this->cpGetRoomByRoomCode($pUpdateValues['room_code']);
        if ($mRoom) {
            $mSaved = $mRoom->setTranslation($pUpdateValues['attribute'], $pUpdateValues['lang'], $pUpdateValues['value'])->save();
        }
        return $mSaved;
    }
    public function cpIndexModel($pClientID)
    {
        $mModel = $this->model->get();
        if (!count($mModel)) {
            $mModel = $this->cpSendApiRequest(
                'hotel-content-api/1.0/types/rooms',
                RequestTypes::GET,
                $pClientID
            );
            $this->cpMassCreate($mModel->json()["rooms"]);
            return $mModel->json()["rooms"];
        }
        return $mModel;
    }

    //Need to remove
    public function cpMassCreate(array $pModelArr)
    {
        $mUpsertArr = array_map(function ($pModel) {
            $arr = [
                'room_code' => $pModel['code'],
                'type' => $pModel['type'],
                'characteristic' => $pModel['characteristic'],
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
