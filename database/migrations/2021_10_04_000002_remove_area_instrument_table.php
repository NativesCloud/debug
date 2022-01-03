<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveAreaInstrumentTable extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('area_instrument');
    }

    public function down(): void
    {
        Schema::create('area_instrument', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('area_id')->constrained()->cascadeOnDelete();
            $table->foreignId('instrument_id')->constrained()->cascadeOnDelete();
        });
    }
}
