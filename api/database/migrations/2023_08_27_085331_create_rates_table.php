<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('rates', function (Blueprint $table) {
            $table->id();
            $table->string('currency_iso');
            $table->string('base_currency_iso');
            $table->tinyInteger('precision');
            $table->unsignedBigInteger('units');
            $table->timestamp('actual_at');
            $table->timestamp('created_at');

            $table->index(['currency_iso', 'base_currency_iso', 'id', 'actual_at'], 'rates_pair_id_actual_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rates');
    }
};
