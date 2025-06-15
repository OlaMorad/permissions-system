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
        Schema::create('form_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_content_id')->references('id')->on('form_contents');
            $table->string('file_path')->nullable();   // ملف
            $table->string('image_path')->nullable();  // صورة
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('form_media');
    }
};
