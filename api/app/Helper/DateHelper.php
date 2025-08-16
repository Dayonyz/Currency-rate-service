<?php

namespace App\Helper;

use DateTime;

class DateHelper
{
    public static function getLastDayOfMonth(int $year, int $month): int
    {
        return (int) (new DateTime("$year-$month-01"))->format('t');
    }
}