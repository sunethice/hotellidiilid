<?php

namespace App\Models;

use App\Http\Traits\OwnerTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Log;
use Jenssegers\Mongodb\Eloquent\Model;

class Markup extends Model
{
    use HasFactory, OwnerTrait;
    protected $fillable = [
        'country', 'city', 'stay_date', 'markup_pct', 'created_by', 'modified_by'
    ];

    protected $casts = [
        'stay_date' => 'date',
        'markup_pct' => 'double'
    ];

    public function location()
    {
        return $this->belongsTo('App\Models\Country', "country", "country_code");
    }

    public function destination()
    {
        return $this->belongsTo('App\Models\Destination', "city","destination_code");
    }

    public function setStayDateAttribute($value)
    {
        $this->attributes['stay_date'] = date('Y-m-d', strtotime($value));
    }

    public function getStayDateAttribute($value)
    {
        return date('Y-m-d', strtotime($value));
    }
}
