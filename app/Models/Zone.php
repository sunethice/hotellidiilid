<?php

namespace App\Models;

use App\Http\Traits\OwnerTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Log;
use Jenssegers\Mongodb\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Zone extends Model
{
    use HasFactory, HasTranslations, OwnerTrait;
    protected $fillable = ['zone_code', 'name', 'description', 'created_by', 'modified_by', 'client_id'];
    public $translatable = ['description'];

    // public $timestamps = false;
    // protected $visible = ['zone_code', 'name', 'description'];
    protected $hidden = ['created_at', 'updated_at'];

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
