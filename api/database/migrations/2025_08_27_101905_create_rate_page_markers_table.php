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
        Schema::create('rate_page_markers', function (Blueprint $table) {
            $table->id();
            $table->string('currency_iso');
            $table->string('base_currency_iso');
            $table->unsignedMediumInteger('limit');
            $table->unsignedBigInteger('page');
            $table->unsignedBigInteger('since_rate_id');
            $table->unique(['currency_iso', 'base_currency_iso', 'limit', 'page'], 'rate_page_marker_idx');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rate_page_markers');
    }
};
