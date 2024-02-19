<?php

namespace App\Models;

use App\Http\Traits\OwnerTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Jenssegers\Mongodb\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Category extends Model
{
    use HasFactory, HasTranslations, OwnerTrait;
    protected $fillable = ['category_code', 'simple_code', 'accommodation_type', 'group', 'description', 'created_by', 'modified_by'];
    public $translatable = ['accommodation_type', 'description'];

    protected $casts = [
        'simple_code' => 'int'
    ];

    public function categoryGroup()
    {
        return $this->belongsTo('App\Models\Group_category');
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

    public function getAccommodationTypeAttribute($value)
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
