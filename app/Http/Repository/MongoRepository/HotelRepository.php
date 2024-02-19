<?php

namespace App\Http\Repository\MongoRepository;

use App\Http\Enums\RequestTypes;
use App\Http\Repository\Contracts\IHotelRepository;
use App\Http\Traits\HBApiTrait;
use App\Models\Hotel;
use Illuminate\Support\Facades\Log;

class HotelRepository extends BaseRepository implements IHotelRepository
{
    use HBApiTrait;
    private $cFacilityRepository;
    private $cRoomRepository;
    private $cHotelImageRepository;
    public function __construct(Hotel $model, FacilityRepository $pFacilityRepository, RoomRepository $pRoomRepository, HotelImageRepository $pHotelImageRepository)
    {
        parent::__construct($model);
        $this->cFacilityRepository = $pFacilityRepository;
        $this->cRoomRepository = $pRoomRepository;
        $this->cHotelImageRepository = $pHotelImageRepository;
    }

    public function cpGetHotelByCode($pHotelCode)
    {
        $mHotel = $this->model->where('hotel_code', intval($pHotelCode))->first();
        if (isset($mHotel)) {
            $mHotel['facilities'] = $this->cFacilityRepository->cpListFacilitiesByFacilityCodes($mHotel['facilities']);
            $mHotel['rooms'] = $this->cRoomRepository->cpListRoomsByRoomCodes($mHotel['rooms']);
            $mHotel['images'] = $this->cHotelImageRepository->cpGetImagesByHotelID(intval($pHotelCode));
        }
        if (!$mHotel)
            return null;
        return $mHotel;
    }

    public function cpGetHotelByName($pHotelName)
    {
        $mLocale = app()->getLocale();
        $mHotels = $this->model->select('hotel_code', 'name')
        ->where("name", 'like', "%\"$mLocale\":\"%$pHotelName%")->get();
        if (!$mHotels)
            return [];
        return $mHotels;
    }

    public function cpUpdateHotelAttribute($pUpdateValues)
    {
        $mSaved = false;
        $mHotel = $this->cpGetHotelByCode($pUpdateValues['hotel_code']);
        if ($mHotel) {
            $mSaved = $mHotel->setTranslation($pUpdateValues['attribute'], $pUpdateValues['lang'], $pUpdateValues['value'])->save();
        }
        return $mSaved;
    }

    public function cpUpdateActiveStatus($pUpdateValues)
    {
        $mSaved = false;
        $mHotel = $this->cpGetHotelByCode($pUpdateValues['hotel_code']);
        if ($mHotel) {
            $mHotel['status'] = $pUpdateValues['status'];
            $mSaved = $mHotel->save();
        }
        return $mSaved;
    }

    public function cpGetFacilitiesByHotelCode($pHotelCode)
    {
        $mHotel = $this->cpGetHotelByCode($pHotelCode);
        if (!$mHotel || empty($mHotel['facilities'])) {
            return [];
        }
        $mFacilities = $this->cFacilityRepository->cpListFacilitiesByFacilityCodes($mHotel['facilities']->toArray());
        return $mFacilities;
    }

    public function cpListHotelsByCountryCode($pCountryCode)
    {
        $mHotels = $this->model->where('country_code', $pCountryCode)->get();
        $mHotels = $mHotels->map(function ($hotel, $key) {
            $hotel['facilities'] = $this->cFacilityRepository->cpListFacilitiesByFacilityCodes($hotel['facilities']);
            $hotel['rooms'] = $this->cRoomRepository->cpListRoomsByRoomCodes($hotel['rooms']);
            return $hotel;
        });
        if (!$mHotels) {
            return [];
        }
        return $mHotels;
    }

    public function cpListHotelCodesByDestination($pDestinationCode, $pClientID)
    {
        $mHotels = $this->cpSendApiRequest(
            'hotel-content-api/1.0/hotels',
            RequestTypes::GET,
            $pClientID,
            array("destinationCode" => $pDestinationCode)
        );
        if (!$mHotels) {
            return [];
        } else {
            $mHotels  = $mHotels->json()["hotels"];
            $mHotels = array_map(function ($pHotel) {
                return $pHotel["code"];
            }, $mHotels);
        }
        return $mHotels;
    }

    public function cpListHotelCodesByCountry($pCountryCode, $pClientID)
    {
        $mHotels = $this->cpSendApiRequest(
            'hotel-content-api/1.0/hotels',
            RequestTypes::GET,
            $pClientID,
            array("countryCode" => $pCountryCode)
        );
        if (!$mHotels) {
            return [];
        } else {
            $mHotels  = $mHotels->json()["hotels"];
            $mHotels = array_map(function ($pHotel) {
                return $pHotel["code"];
            }, $mHotels);
        }
        return $mHotels;
    }

    public function cpListHotelsByZone($pCountryCode, $pDestCode, $pZoneCode, $pClientID){
        $mHotels = $this->cpSendApiRequest(
            'hotel-content-api/1.0/hotels',
            RequestTypes::GET,
            $pClientID,
            ["countryCode" => $pCountryCode, "destinationCode" => $pDestCode]
        );
        if (!$mHotels) {
            return [];
        } else {
            $mHotels  = $mHotels->json()["hotels"];
            $mHotels = array_filter($mHotels, function ($pHotel) use ($pZoneCode) {
                return $pHotel["zoneCode"] == (int)$pZoneCode;
            });
        }
        return $mHotels;
    }

    public function cpUpdateHotelDetails($pUpdateValues)
    {
        $mHotels = $this->cpGetHotelByCode($pUpdateValues["hotel_code"]);
        if (!$mHotels) {
            return false;
        }
        $mUpdated = $mHotels->update($pUpdateValues);
        return $$mHotels;
    }

    public function cpIndexModel($pClientID)
    {
        // $mModel = $this->model->get();
        // if (!count($mModel)) {
            $from = 1;
            $to = 1000;
        while ($from <= 2517) {
                $mModel = $this->cpSendApiRequest(
                    'hotel-content-api/1.0/hotels',
                    RequestTypes::GET,
                    $pClientID,
                ["destinationCode" => "PAR"],
                    $from,
                    $to
                );
            if (isset($mModel->json()["hotels"])) {
                $this->cpMassCreate($mModel->json()["hotels"]);
            } else {
                Log::info($mModel->json());
            }
                $from += 1000;
                $to += 1000;
        }
        return $mModel->json()["hotels"];
        // }
        return $mModel;
    }

    //Need to remove
    public function cpMassCreate(array $pModelArr)
    {
        $mUpsertArr = array_map(function ($pModel) {
            $arr = [
                'hotel_code' => isset($pModel['code']) ? $pModel['code'] : "",
                'name' => isset($pModel['name']['content']) ? $pModel['name']['content'] : "",
                'description' => isset($pModel['description']['content']) ? $pModel['description']['content'] : "",
                'longitude' => isset($pModel['coordinates']['longitude']) ? $pModel['coordinates']['longitude'] : null,
                'latitude' => isset($pModel['coordinates']['latitude']) ? $pModel['coordinates']['latitude'] : null,

                'country_code' => isset($pModel['countryCode']) ? $pModel['countryCode'] : "",
                // 'iso_code' => array_key_exists('isoCode', $pModel) ? $pModel['isoCode'] : "",
                'state_code' => isset($pModel['stateCode']) ? $pModel['stateCode'] : "",
                'destination_code' => isset($pModel['destinationCode']) ? $pModel['destinationCode'] : "",
                'zone_code' => isset($pModel['zoneCode']) ? $pModel['zoneCode'] : "",
                'category_code' => isset($pModel['categoryCode']) ? $pModel['categoryCode'] : "",

                'address_number' => isset($pModel['address']['number']) ? $pModel['address']['number'] : "",
                'address_street' => isset($pModel['address']['street']) ? $pModel['address']['street'] : "",
                'address_city' => isset($pModel['city']['content']) ? $pModel['city']['content'] : "",
                'address_postal_code' => isset($pModel['postalCode']) ? $pModel['postalCode'] : "",

                'active' => true,
                'email' => isset($pModel['email']) ? $pModel['email'] : "",
                'phones' => isset($pModel['phones']) ? $pModel['phones'] : [],
                'boards' => isset($pModel['boardCodes']) ? $pModel["boardCodes"] : [],
                'rooms' => isset($pModel['rooms']) ? array_column($pModel['rooms'], 'roomCode') : [],
                'facilities' => isset($pModel['facilities']) ? array_column($pModel['facilities'], 'facilityCode') : []
            ];
            $mSaved = $this->model->create($arr);
            if (isset($pModel["images"])) {
                foreach ($pModel["images"] as $image) {
                    $mSaved->images()->create([
                        'type_code' => isset($image['imageTypeCode']) ? $image["imageTypeCode"] : "",
                        'type_description' => isset($image['imageTypeCode']) ? $image["imageTypeCode"] : "",
                        'path' => isset($image['path']) ? $image["path"] : "",
                        'order' => isset($image['order']) ? $image["order"] : "",
                        'visual_order' => isset($image['visualOrder']) ? $image["visualOrder"] : "",
                        'hotel_code' => isset($pModel['code']) ? $pModel['code'] : "",
                        'active' => true
                    ]);
                }
            }
            return $pModel;
        }, $pModelArr);
        return true;
    }
}
