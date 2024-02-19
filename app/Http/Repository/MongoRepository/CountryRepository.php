<?php

namespace App\Http\Repository\MongoRepository;

use App\Http\Enums\RequestTypes;
use App\Http\Repository\Contracts\ICountryRepository;
use App\Http\Traits\HBApiTrait;
use App\Models\Country;
use Hamcrest\Core\IsNull;
use Illuminate\Support\Facades\Log;
use PhpParser\Node\Expr\BinaryOp\Equal;

use function PHPUnit\Framework\isNull;

class CountryRepository extends BaseRepository implements ICountryRepository
{
    use HBApiTrait;
    public function __construct(Country $model)
    {
        parent::__construct($model);
    }

    public function cpIndex()
    {
        $mCountries = $this->model->raw(function ($collection) {
            return $collection->aggregate([
                ['$project' => [
                    'country_code' => '$country_code',
                    'description' => [
                        '$cond' => [
                            'if' => [
                                '$eq' => ['$custom_description', '{"en":""}']
                            ],
                            'then' => '$description',
                            'else' => '$custom_description'
                        ]
                    ],
                    'iso_code' => '$iso_code'
                ]]
            ]);
        });
        if (!$mCountries)
            return [];
        return $mCountries;
    }

    public function cpGetCountryByCode($pCountryCode)
    {
        $mCountry = $this->model->where('country_code', $pCountryCode)->first();
        if (!$mCountry)
            return null;
        return $mCountry;
    }

    public function cpGetCountriesByPhrase($pCountryPhrase)
    {
        $mLocale = app()->getLocale();
        $mCountries = $this->model->select('country_code', 'description', 'custom_description')
            // ->whereRaw("JSON_EXTRACT(description, '$.$mLocale') = '$pCountryPhrase'")
            ->where("custom_description", 'like', "%\"$mLocale\":\"%$pCountryPhrase%")
            ->orWhere("description", 'like', "%\"$mLocale\":\"%$pCountryPhrase%")
            ->get();
        if (!$mCountries)
            return null;
        return $mCountries;
    }

    public function cpUpdateCountryDescr($pUpdateValues)
    {
        $mSaved = false;
        $mCountry = $this->cpGetCountryByCode($pUpdateValues['country_code']);
        if ($mCountry) {
            if ($pUpdateValues['lang'] == "en") {
                $mSaved = $mCountry->setTranslation('custom_description', $pUpdateValues['lang'], $pUpdateValues['description'])->save();
            } else {
                if ($mCountry->getTranslation('custom_description', 'en') == "") {
                    $mSaved = $mCountry
                        ->setTranslation('custom_description', 'en', $mCountry->description)
                        ->setTranslation('custom_description', $pUpdateValues['lang'], $pUpdateValues['description'])
                        ->save();
                } else {
                    $mSaved = $mCountry->setTranslation('custom_description', $pUpdateValues['lang'], $pUpdateValues['description'])->save();
                }
            }
        }
        return $mSaved;
    }

    public function cpListStatesByCountry($pCountryCode)
    {
        $mCountry = $this->cpGetCountryByCode($pCountryCode);
        $mStatesList = null;
        if ($mCountry) {
            $mStatesList = $mCountry->states()->get();
        }
        return $mStatesList;
    }

    public function cpIndexCountry($pClientID)
    {
        $mCountries = $this->model->get();
        if (!count($mCountries)) {
            $mCountries = $this->cpSendApiRequest(
                'hotel-content-api/1.0/locations/countries',
                RequestTypes::GET,
                $pClientID
            );
            $this->cpMassCreate($mCountries->json()["countries"]);
            return $mCountries->json()["countries"];
        }
        return $mCountries;
    }

    //Need to remove
    public function cpMassCreate(array $pDestArr)
    {
        $mUpsertArr = array_map(function ($pDest) {
            $arr = [
                "country_code" => $pDest["code"],
                "description" => $pDest["description"]['content'],
                "custom_description" => "",
                "iso_code" => $pDest["isoCode"],
            ];
            // if ($pDest["code"] == "AE") {
            $mSaved = $this->model->create($arr);
            if (isset($pDest["states"])) {
                foreach ($pDest["states"] as $state) {
                    $mSaved->states()->create([
                        'state_code' => $state["code"],
                        'name' => $state["name"],
                    ]);
                }
            }
            return $pDest;
        }, $pDestArr);
        return true;
    }
}
