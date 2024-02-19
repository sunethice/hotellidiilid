<?php

namespace App\Models;

use App\Http\Traits\OwnerTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Jenssegers\Mongodb\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Facility extends Model
{
    use HasFactory, HasTranslations, OwnerTrait;
    protected $fillable = ['facility_code', 'facility_group_code', 'description', 'created_by', 'modified_by'];
    public $translatable = ['description'];
    protected $casts = [
        'facility_code' => 'int',
        'facility_group_code' => 'int'
    ];

    public function facilityGroup()
    {
        return $this->belongsTo('App\Models\Facility_group');
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
