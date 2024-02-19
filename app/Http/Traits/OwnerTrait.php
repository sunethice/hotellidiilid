<?php

namespace App\Http\Traits;

use Illuminate\Support\Facades\Auth;

trait OwnerTrait
{
    public static function bootOwnerTrait()
    {
        static::creating(function ($model) {
            $user = Auth::guard('api')->user();
            $model->created_by = $user->id ?? "";
            if (request('client_id'))
                $model->client_id = request('client_id');
            else
                $model->client_id = request()->user()->token()->client_id;
        });

        static::updating(function ($model) {
            $model->modified_by = Auth::user()->id;
        });
    }
}
