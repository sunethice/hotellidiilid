<?php

namespace App\Models;

use App\Http\Traits\OwnerTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Log;
use Jenssegers\Mongodb\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Country extends Model
{
    use HasFactory, HasTranslations, OwnerTrait;
    protected $fillable = ['country_code', 'iso_code', 'description', 'custom_description', 'created_by', 'modified_by', 'client_id'];
    public $translatable = ['description', 'custom_description'];
    //new
    // protected $casts = [
    //     'states' => 'array',
    // ];
    //new
    // public $timestamps = false;
    protected $hidden = ['created_at', 'updated_at'];

    public function destinations()
    {
        return $this->hasMany('App\Models\Destination');
    }

    //new
    public function states()
    {
        return $this->embedsMany('App\Models\State');
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

    public function getCustomDescriptionAttribute($value)
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
