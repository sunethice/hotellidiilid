<?php

namespace App\Models;

use App\Http\Traits\OwnerTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Jenssegers\Mongodb\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Booking extends Model
{
    use HasFactory, HasTranslations, OwnerTrait;
    protected $fillable = [
        'reference', 'client_reference', 'status', 'cancellation_policy', 'modification_policy', "used_hb_key",
        'holder_name', 'holder_surname', 'remark', 'total_net', 'net_with_markup', 'pending_amount', 'country_code',
        'destination_code', 'currency', 'hotel', 'check_in', 'check_out', 'contact_details', 'client_id', 'created_by', 'modified_by'
    ];


    public $translatable = ['description'];

    protected $casts = [
        'cancellation_policy' => 'boolean',
        'modification_policy' => 'boolean',
        'total_net' => 'double',
        'net_with_markup' => 'double',
        'pending_amount' => 'double'
    ];
}
