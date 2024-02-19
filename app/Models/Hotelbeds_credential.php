<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Jenssegers\Mongodb\Eloquent\Model;

class Hotelbeds_credential extends Model
{
    use HasFactory;
    protected $fillable = ['client_id', 'hb_api_key', 'hb_api_secret'];
}
