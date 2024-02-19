<?php

namespace App\Http\Enums;

use BenSampo\Enum\Enum;

final class BookingListBy extends Enum
{
    const CHECKIN = 'CHECKIN';
    const CHECKOUT = 'CHECKOUT';
    const CREATION = 'CREATION';
}
