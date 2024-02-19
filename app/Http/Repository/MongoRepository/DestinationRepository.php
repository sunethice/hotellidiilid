<?php

namespace App\Http\Repository\MongoRepository;

use App\Http\Enums\RequestTypes;
use App\Http\Repository\Contracts\IDestinationRepository;
use App\Models\Destination;
use Illuminate\Support\Facades\Log;
use App\Http\Traits\HBApiTrait;

class DestinationRepository extends BaseRepository implements IDestinationRepository
{
    use HBApiTrait;
    public function __construct(Destination $model)
    {
        parent::__construct($model);
    }

    public function cpGetDestByCode($pDestinationCode)
    {
        $mDestination = $this->model->where('destination_code', $pDestinationCode)->first();
        if (!$mDestination)
            return null;
        return $mDestination;
    }

    public function cpGetDestByZoneCode($pDestinationCode, $pZoneCode)
    {
        $mDestination = $this->model
            ->where('destination_code', $pDestinationCode)
            ->where('zones.zone_code', intval($pZoneCode))
            ->first();
        if (!$mDestination)
            return null;
        return $mDestination;
    }

    public function cpGetDestByPhrase($pDestinationPhrase)
    {
        $mLocale = app()->getLocale();
        $mDestination = $this->model->select('destination_code', 'name', 'country_code')
        // ->where("custom_description", 'like', "%\"$mLocale\":\"%$pDestinationPhrase%")
        // ->where("name", 'like', "%$pDestinationPhrase%")->get();
        ->where("name", 'like', "%$pDestinationPhrase%")->with(
            ['country' => function ($query) {
                $query->select('country_code', 'iso_code', 'description', 'custom_description');
            }]
        )->get();
        if (!$mDestination)
            return null;
        return $mDestination;
    }

    public function cpGetDestByPhraseWithCountry($pDestinationPhrase)
    {
        $mDestination = $this->model->where('name', 'like', "%$pDestinationPhrase%")->with('country')->first();
        if (!$mDestination)
            return null;
        return $mDestination;
    }

    public function cpGetDestWithCountry($pDestinationCode)
    {
        $mDestination = $this->model->where('destination_code', 'like', $pDestinationCode)->with('country')->first();
        if (!$mDestination)
            return null;
        return $mDestination;
    }

    public function cpUpdateDestAttribute($pUpdateValues)
    {
        $mSaved = false;
        $mDestination = $this->cpGetDestByCode($pUpdateValues['destination_code']);
        if ($mDestination) {
            $mSaved = $mDestination->setTranslation($pUpdateValues['attribute'], $pUpdateValues['lang'], $pUpdateValues['value'])->save();
        }
        return $mSaved;
    }

    public function cpUpdateZoneDescr($pUpdateValues)
    {
        $mSaved = false;
        $mDestination = $this->cpGetDestByZoneCode($pUpdateValues['destination_code'], $pUpdateValues['zone_code']);
        if ($mDestination) {
            foreach ($mDestination->zones as $zone) {
                if ($zone->zone_code == $pUpdateValues['zone_code']) {
                    $mSaved = $zone->setTranslation('description', $pUpdateValues['lang'], $pUpdateValues['description'])->save();
                }
            }
        }
        return $mSaved;
    }

    public function cpListZonesByDest($pDestinationCode)
    {
        $mDestination = $this->cpGetDestByCode($pDestinationCode);
        $mZonesList = null;
        if ($mDestination) {
            $mZonesList = $mDestination->zones()->get();
        }
        return $mZonesList;
    }

    public function cpIndex()
    {
        $mDestinations = $this->model->select('country_code', 'destination_code', 'name')->with('zones')->get();
        if (!$mDestinations)
            return [];
        return $mDestinations;
    }

    public function cpIndexByCountryCode($pCountryCode)
    {
        $mDestinations = $this->model->select('country_code', 'destination_code', 'name')->where('country_code', $pCountryCode)->get();
        if (!$mDestinations)
            return [];
        return $mDestinations;
    }

    public function cpGetCountryCodeOfDest($pDestCode)
    {
        $mDestination = $this->model->select('country_code')->where('destination_code', $pDestCode)->first();
        if (!$mDestination)
            return null;
        return $mDestination;
    }

    public function cpIndexDestinations($pClientID)
    {
        $mDestinations = $this->model->get();
        // if (!$mDestinations->count()) {
        $mDestinations = $this->cpSendApiRequest(
            'hotel-content-api/1.0/locations/destinations',
            RequestTypes::GET,
            $pClientID
        );
        $this->cpMassCreate($mDestinations->json()["destinations"]);
        return $mDestinations->json()["destinations"];
        // }
        return $mDestinations;
    }

    public function cpMassCreate(array $pDestArr)
    {
        $mUpsertArr = array_map(function ($pDest) {
            $arr = [
                "country_code" => $pDest["countryCode"],
                "destination_code" => $pDest["code"],
                "name" => array_key_exists('name', $pDest) ? $pDest["name"]["content"] : "",
                "iso_code" => $pDest["isoCode"],
                "description" => ""
                // "zones" => $pDest["zones"]
            ];
            // if ($pDest["code"] == "1AT") {
            $mSaved = $this->model->create($arr);
            foreach ($pDest["zones"] as $zone) {
                $arr2 = [
                    "destination_code" => $pDest["code"],
                    'zone_code' => $zone["zoneCode"],
                    'name' => $zone["name"],
                    'description' => ""
                ];
                $mSaved->zones()->create($arr2);
                $arr2["lang"] = "nl";
                $this->cpUpdateZoneDescr($arr2);
            }
            $arr["lang"] = "en";
            $arr["attribute"] = "description";
            $arr["value"] = "";
            $this->cpUpdateDestAttribute($arr);
            // }
            // $this->model->insert($arr);
            return $pDest;
        }, $pDestArr);
        return true;
    }

    // public function cpMassCreate(array $pDestArr)
    // {
    //     $mUpsertArr = array_map(function ($pDest) {
    //         $arr = [
    //             "country_code" => $pDest["code"],
    //             "description" => $pDest["description"]['content'],
    //             "custom_description" => "",
    //             "iso_code" => $pDest["isoCode"],
    //             // "states" => $pDest["states"]
    //         ];
    //         if ($pDest["code"] == "AE") {
    //             $mSaved = $this->model->create($arr);
    //             foreach ($pDest["states"] as $state) {
    //                 $mSaved->states()->create([
    //                     'state_code' => $state["code"],
    //                     'name' => $state["name"],
    //                 ]);
    //             }
    //             $arr["lang"] = "en";
    //             $this->cpUpdateDescrByLang($arr);
    //         }
    //         return $pDest;
    //     }, $pDestArr);
    //     return true;
    // }
}
