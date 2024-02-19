<?php

namespace App\Models;

use App\Http\Traits\OwnerTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Jenssegers\Mongodb\Eloquent\Model;

class CancellationRequests extends Model
{
    use HasFactory, OwnerTrait;
    protected $fillable = ['reference', 'status','created_by','modified_by'];
    protected $hidden = ['created_at', 'updated_at'];
}
