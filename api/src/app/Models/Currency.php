<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Currency extends Model
{
    const UPDATED_AT = null;

    use HasFactory;

    public function rates(): HasMany
    {
        return $this->hasMany(Rate::class, 'currency_id');
    }

    public function ratesByBase(): HasMany
    {
        return $this->hasMany(Rate::class, 'base_currency_id', 'id');
    }
}
