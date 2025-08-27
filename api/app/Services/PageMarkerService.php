<?php

namespace App\Services;

use InvalidArgumentException;

class PageMarkerService
{
    public static function getPageByLimits(int $index, array $limits): array
    {
        $limits = array_unique(array_filter($limits, fn($item) => is_int($item)));

        if (empty($limits)) {
            throw new InvalidArgumentException(
                'Input parameter $limit must be array of integers and can not be empty.'
            );
        }

        sort($limits);

        return array_combine(
            $limits,
            array_map(fn($item) => (int) ceil($index / $item), $limits)
        );
    }

    public static function isFirstItemOnPage(int $index, int $limit, int $page): bool
    {
        return $index - $limit*($page - 1) === 1;
    }
}