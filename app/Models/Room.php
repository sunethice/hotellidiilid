<?php

namespace App\Models;

use App\Http\Traits\OwnerTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Log;
use Jenssegers\Mongodb\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Room extends Model
{
    use HasFactory, HasTranslations, OwnerTrait;
    protected $fillable = [
        'room_code', 'type', 'type_description', 'characteristic', 'characteristic_description',
        'min_pax', 'max_pax', 'max_adults', 'max_children', 'min_adults', 'description', 'created_by', 'modified_by'
    ];
    public $translatable = ['type_description', 'characteristic_description', 'description'];

    protected $casts = [
        'min_pax' => 'int',
        'max_pax' => 'int',
        'max_adults' => 'int',
        'max_children' => 'int',
        'min_adults' => 'int'
    ];

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

    public function getTypeDescriptionAttribute($value)
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

    public function getCharacteristicDescriptionAttribute($value)
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
