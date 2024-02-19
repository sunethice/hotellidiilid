<?php

namespace App\Http\Repository\MongoRepository;

use App\Http\Repository\Contracts\IGeneralConfigRepository;
use App\Models\General_config;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class GeneralConfigRepository extends BaseRepository implements IGeneralConfigRepository
{
    public function __construct(General_config $model)
    {
        parent::__construct($model);
    }

    public function cpSetGeneralMarkup($pUpdateValues, $pClientID)
    {
        $mExistingRecord = $this->model
            ->where('client_id', $pClientID)
            ->where('config_type', $pUpdateValues['config_type'])->first();
        if (!$mExistingRecord) {
            $recordValues = [
                "config_type" => $pUpdateValues["config_type"],
                "configuration" => $pUpdateValues["configuration"],
                "client_id" => $pClientID
            ];
            $mCreated = $this->model->create($recordValues);
            return $mCreated;
        } else {
            $mExistingRecord["configuration"] = $pUpdateValues["configuration"];
            $mSaved = $mExistingRecord->save();
            return $mSaved;
        }
    }

    public function cpGetGeneralMarkup($pClientID, $pConfigType)
    {
        $mRecord = $this->model
            ->where('client_id', $pClientID)
            ->where('config_type', $pConfigType)->first();
        return $mRecord;
    }
}
