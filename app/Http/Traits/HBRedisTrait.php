<?php

namespace App\Http\Traits;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

trait HBRedisTrait
{
    /*
        might be useful
            - https://divinglaravel.com/introduction-to-redis
            - https://www.w3resource.com/laravel/redis.php
    */
    public function cpGenerateKey($pKeyValues): String
    {
        $mCacheKey = $pKeyValues['check_in'] . ":" . $pKeyValues['check_out'];
        if (isset($pKeyValues['destination_code'])) {
            $mCacheKey .= ":" . $pKeyValues['destination_code'];
        } else if (isset($pKeyValues['country_code'])) {
            $mCacheKey .= ":" . $pKeyValues['country_code'];
        } else if (isset($pKeyValues['hotel_codes'])) {
            $mStrHotelCodes = array_reduce($pKeyValues['hotel_codes'], function ($v1, $v2) {
                if (empty($v1))
                    return $v2;
                else
                    return $v1 . "~~" . $v2;
            }, "");
            $mCacheKey .= ":" . $mStrHotelCodes;
        }
        foreach ($pKeyValues['occupancies'] as $occupancy) {
            $mKeyOccupancy = $occupancy['room_no'] . "~" . $occupancy['adults'] . "~" . $occupancy['children'];
            if (!empty($occupancy['children_age'])) {
                $mKeyOccupancy = "[" . $mKeyOccupancy . ":[" . implode("-", $occupancy['children_age']) . "]]";
            }
            $mCacheKey .= ":" . $mKeyOccupancy;
        }
        return $mCacheKey; //"product:1:sales";
    }

    public function cpCacheHotelSearch($pCacheKey, $pCacheDetails)
    {
        //key example 'user:1:notified'
        Redis::set($pCacheKey, $pCacheDetails, 'EX', 900); //expire in 15min
    }

    public function cpGetCachedHotel($pCacheKey)
    {
        return Redis::get($pCacheKey);
    }

    public function cpCacheHotelMeta($pCacheKey, $pCacheDetails)
    {
        //key example 'user:1:notified'
        Redis::set($pCacheKey . "_meta", $pCacheDetails, 'EX', 900); //expire in 15min
    }

    public function cpGetCachedHotelMeta($pCacheKey)
    {
        return Redis::get($pCacheKey . "_meta");
    }

    public function cpKeyExists($pCacheKey)
    {
        return Redis::exists($pCacheKey);
    }

    public function cpDeleteKey($pCacheKey)
    {
        Redis::del($pCacheKey);
    }

    public function cpListKeys()
    {
        return Redis::keys('*');
    }

    public function cpDeleteAll(){
        Redis::flushDB();
    }
}
