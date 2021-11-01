<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class NewFormatCreateGroupFormsTable extends Migration
{
    public function up(): void
    {
        Schema::create('group_forms', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->softDeletes();
            $table->string('name');
            $table->boolean('custom')->default(true);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('group_forms');
    }
}
