<?php

namespace App\Enums\Traits;

use InvalidArgumentException;

trait NamesTrait
{
    public static function getNames(): array
    {
        return array_column(self::cases(), 'name');
    }

    public static function fromName(string $name): static
    {
        foreach (self::cases() as $enum) {
            if ($name === $enum->name) {
                return $enum;
            }
        }

        throw new InvalidArgumentException("$name is not a valid backing name for enum " . self::class);
    }
}