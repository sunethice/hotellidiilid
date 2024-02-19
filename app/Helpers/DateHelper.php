<?php

namespace App\Helpers;

use DateInterval;
use DatePeriod;
use DateTime;

class DateHelper
{
    public static function cpListDatesFromRange($start, $end, $format = 'Y-m-d')
    {
        $array = array();
        $interval = new DateInterval('P1D'); //date interval of period 1 day
        $realEnd = new DateTime($end);
        $realEnd->add($interval);
        $period = new DatePeriod(new DateTime($start), $interval, $realEnd);
        foreach ($period as $date) {
            $array[] = $date->format($format);
        }
        return $array;
    }
}
