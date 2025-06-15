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
        Schema::create('form_path', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_id')->references('id')->on('forms');
            $table->foreignId('path_id')->references('id')->on('paths');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('form_path');
    }
};
