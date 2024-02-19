<?php

namespace App\Models;

use App\Http\Traits\OwnerTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Jenssegers\Mongodb\Eloquent\Model;

class General_config extends Model
{
    use HasFactory, OwnerTrait;
    protected $fillable = [
        'config_type', 'configuration', 'client_id', 'created_by', 'modified_by'
    ];

    //consfig_type = General_Markup for general markup
}
