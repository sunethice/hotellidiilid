<?php

namespace App\Http\Enums;

use BenSampo\Enum\Enum;

final class CancelRequestStatus extends Enum
{
    const ALL = 'ALL';
    const PENDING = 'PENDING';
    const PROCESSED = 'PROCESSED';
    const DECLINED = 'DECLINED';
}
