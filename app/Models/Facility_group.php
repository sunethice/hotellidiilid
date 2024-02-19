<?php

namespace App\Models;

use App\Http\Traits\OwnerTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Jenssegers\Mongodb\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Facility_group extends Model
{
    use HasFactory, HasTranslations, OwnerTrait;
    protected $fillable = ['facility_group_code', 'description', 'created_by', 'modified_by'];
    public $translatable = ['description'];
    protected $casts = [
        'facility_group_code' => 'int'
    ];

    public function facility()
    {
        return $this->hasMany('App\Models\Facility');
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
}
