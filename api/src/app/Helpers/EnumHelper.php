<?php

namespace App\Helpers;

class EnumHelper
{
    public static function caseByName(array $cases, string $needle, ?string $errorMessage = null)
    {
        $case = array_filter($cases, function ($item) use ($needle) {
            return $item->name === $needle;
        });

        if (count($case))
            return array_shift($case);

        throw new \InvalidArgumentException($errorMessage ? : "Invalid enum case name: $needle");
    }
}