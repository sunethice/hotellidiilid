<?php

namespace App\Models;

use App\Http\Traits\OwnerTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Jenssegers\Mongodb\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Group_category extends Model
{
    use HasFactory, HasTranslations, OwnerTrait;
    protected $fillable = ['group_code', 'order', 'name', 'description', 'created_by', 'modified_by'];
    public $translatable = ['name', 'description'];

    protected $casts = [
        'order' => 'int'
    ];

    public function category()
    {
        return $this->hasMany('App\Models\Category');
    }
}
