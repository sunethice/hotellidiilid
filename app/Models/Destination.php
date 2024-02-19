<?php

namespace App\Models;

use App\Http\Traits\OwnerTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Log;
use Jenssegers\Mongodb\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Destination extends Model
{
    use HasFactory, HasTranslations, OwnerTrait;
    protected $fillable = ['country_code', 'destination_code', 'name', 'description', 'iso_code', 'created_by', 'modified_by', 'client_id'];
    public $translatable = ['name', 'description'];

    protected $hidden = ['created_at', 'updated_at'];

    public function country()
    {
        return $this->belongsTo('App\Models\Country', "country_code", "country_code");
    }

    public function zones()
    {
        // return $this->embedsMany('App\Models\Zone');
        return $this->hasMany('App\Models\Zone');
    }

    public function getNameAttribute($value)
    {
        $mlocale = app()->getLocale();
        $mValue = json_decode($value);
        if (isset($mValue->$mlocale))
            return $mValue->$mlocale;
        else if (isset($mValue->en))
            return $mValue->en;
        else
            return $value;
    }

    public function setNameAttribute($value)
    {
        $mJsonVal = $this->asJson($value);
        $subVal = substr($mJsonVal,1,strlen($mJsonVal)-2); 
        $this->attributes['name'] = $subVal;
    }

    public function getDescriptionAttribute($value)
    {
        $mlocale = app()->getLocale();
        $mValue = json_decode($value);
        if (isset($mValue->$mlocale))
            return $mValue->$mlocale;
        else if (isset($mValue->en))
            return $mValue->en;
        else
            return $value;
    }

    public function getZonesDescription($value)
    {
        // Log::info('getZonesDescription');
    }

     /**
     * Encode the given value as JSON.
     *
     * @param  mixed  $value
     * @return string
     */
    protected function asJson($value)
    {
        return json_encode($value, JSON_UNESCAPED_UNICODE);
    }
}
