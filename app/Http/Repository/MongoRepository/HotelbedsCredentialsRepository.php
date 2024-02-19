<?php

namespace App\Http\Repository\MongoRepository;

use App\Http\Repository\Contracts\IHotelbedsCredentialsRepository;
use App\Models\Hotelbeds_credential;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class HotelbedsCredentialsRepository extends BaseRepository implements IHotelbedsCredentialsRepository
{
    public function __construct(Hotelbeds_credential $model)
    {
        parent::__construct($model);
    }

    public function cpAddCredentials($pHBValues)
    {
        $mClient = DB::table('oauth_clients')->where('_id', $pHBValues['client_id'])->first();
        if (!is_null($mClient)) {
            $mSaved = $this->cpCreate([
                'client_id' => $pHBValues['client_id'],
                'hb_api_key' => $pHBValues['hb_api_key'],
                'hb_api_secret' => $pHBValues['hb_api_secret']
            ]);
            return $mSaved;
        }
        return false;
    }
}
