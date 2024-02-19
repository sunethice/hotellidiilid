<?php

namespace App\Models;

use App\Http\Traits\OwnerTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Jenssegers\Mongodb\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Hotel extends Model
{
    use HasFactory, HasTranslations, OwnerTrait;
    protected $fillable = [
        'hotel_code', 'name', 'description', 'longitude', 'latitude',
        'country_code', 'iso_code', 'state_code', 'destination_code', 'zone_code', 'category_code',
        'address_number', 'address_street', 'address_city', 'address_postal_code',
        'active', 'email', 'phones', 'boards', 'rooms', 'facilities', 'created_by', 'modified_by'
    ];

    /*
        Get:
            Country_description from country_code
            state_name from state_code
            destination_name from destination_code
            Zone_name from zone_code
            category_description from category+code
    */

    public $translatable = ['name', 'description'];

    protected $casts = [
        // 'phones' => 'array',
        // 'boards' => 'array',
        // 'rooms' => 'array',
        // 'facilities' => 'array',
        'active' => 'boolean',
        'longitude' => 'double',
        'latitude' => 'double'
    ];

    public function images()
    {
        return $this->hasMany('App\Models\Hotelimage', 'hotel_code', 'hotel_code');
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
}
