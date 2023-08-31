<?php

namespace App\Dto;

class PaginationTotals
{
    public function __construct(
        private readonly int $pagesCount,
        private readonly int $total
    ){
    }

    public function getPagesCount(): int
    {
        return $this->pagesCount;
    }

    public function getTotal(): int
    {
        return $this->total;
    }
}