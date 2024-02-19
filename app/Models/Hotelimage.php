<?php

namespace App\Models;

use App\Http\Traits\OwnerTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Jenssegers\Mongodb\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Hotelimage extends Model
{
    use HasFactory, HasTranslations, OwnerTrait;
    protected $fillable = [
        'type_code', 'type_description', 'path', 'order', 'visual_order', 'hotel_code', 'active', 'created_by', 'modified_by'
    ];
    public $translatable = ['type_description'];

    // protected $casts = [
    //     'states' => 'array',
    // ];
    protected $casts = [
        'order' => 'int',
        'visual_order' => 'int',
        'hotel_code' => 'int',
        'active' => 'boolean'
    ];

    public function hotel()
    {
        $this->belongsTo('App\Models\Hotel', 'hotel_code');
    }
}
