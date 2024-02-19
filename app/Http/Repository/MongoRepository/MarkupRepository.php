<?php

namespace App\Http\Repository\MongoRepository;

use App\Helpers\CollectionHelper;
use App\Helpers\DateHelper;
use App\Http\Repository\Contracts\IMarkupRepository;
use App\Http\Traits\HBRedisTrait;
use App\Models\Markup;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class MarkupRepository extends BaseRepository implements IMarkupRepository
{
    use HBRedisTrait;
    public function __construct(Markup $model)
    {
        parent::__construct($model);
    }

    public function cpCreateMarkup($InputValues)
    {
        $mInput = [];
        $mInput["country"] = $InputValues["country"];
        if (isset($InputValues["city"]))
            $mInput["city"] = $InputValues["city"];
        else
            $mInput["city"] = null;
        $mDates = DateHelper::cpListDatesFromRange($InputValues["from"], $InputValues["to"]);
        foreach ($mDates as $date) {
            $mInput["stay_date"] = date('Y-m-d', strtotime($date));
            $mRecord = $this->model
                ->where('country', $mInput["country"])
                ->where('city', $mInput["city"])
                ->where('stay_date', $mInput["stay_date"])
                ->first();
            $mDay = strtolower(date('l', strtotime($date)));
            if(isset($mRecord) && $InputValues['overwite_zero']){
                $mRecord["markup_pct"] = 0;
                $mRecord->save();
            }
            else if (isset($InputValues[$mDay . "_pct"]) && $InputValues[$mDay . "_pct"] !== 0) {
                if (!$mRecord) {
                    $mInput["markup_pct"] = $InputValues[$mDay . "_pct"];
                    $this->model->create($mInput);
                } else {
                    $mRecord["markup_pct"] = $InputValues[$mDay . "_pct"];
                    $mRecord->save();
                }
            }
        }
        $this->cpDeleteAll();
        return true;
    }

    public function cpSearchMarkup($pStayDate, $pCountry = null, $pCity = null)
    {
        if (!is_null($pCity))
            $mMarkup = $this->model
                ->where('stay_date', $pStayDate)
                ->where('city', $pCity)->first();
        else
            $mMarkup = $this->model
                ->where('stay_date', $pStayDate)
                ->where('country', $pCountry)
                ->where('city', null)->first();
        return $mMarkup;
    }

    public function cpAdvSearchMarkups($pStartDate, $pEndDate, $pCountry = null, $pCity = null)
    {
        if (!is_null($pCity))
            $mMarkups = $this->model
                ->whereBetween('stay_date', [$pStartDate, $pEndDate])
                ->where('city', $pCity)->with('location')->with('destination')->get();
        else
            $mMarkups = $this->model
                ->whereBetween('stay_date', [$pStartDate, $pEndDate])
                ->where('country', $pCountry)
                ->where('city', null)->with('location')->get();
        $mMarkups = CollectionHelper::paginate($mMarkups, 10);
        return $mMarkups;
    }

    public function cpUpdateMarkup($pUpdateValues)
    {
        $mRecord = $this->model
            ->where('country', $pUpdateValues["country"])
            ->where('city', $pUpdateValues["city"])
            ->where('stay_date', $pUpdateValues["stay_date"])->first();
        $mRecord["markup_pct"] = $pUpdateValues["markup_pct"];
        $isUpdated = $mRecord->save();
        if($isUpdated){
            $this->cpDeleteAll();
        }
        return $isUpdated;
    }
}
