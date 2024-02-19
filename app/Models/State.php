<?php

namespace App\Models;

use App\Http\Traits\OwnerTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Jenssegers\Mongodb\Eloquent\Model;
// use Spatie\Translatable\HasTranslations;

class State extends Model
{
    use HasFactory, OwnerTrait;
    protected $fillable = ['state_code', 'name', 'created_by', 'modified_by'];
    // public $translatable = ['description'];

    // public $timestamps = false;
    protected $visible = ['state_code', 'name'];
}
