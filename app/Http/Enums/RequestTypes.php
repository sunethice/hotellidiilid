<?php

namespace App\Http\Enums;

use BenSampo\Enum\Enum;

final class RequestTypes extends Enum
{
    const GET = 'get';
    const POST = 'post';
    const PUT = 'put';
    const PATCH = 'patch';
    const DELETE = 'delete';
}
