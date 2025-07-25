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
            $table->foreignId('form_content_id')->references('id')->on('form_contents')->cascadeOnDelete();
            $table->string('file')->nullable();   // ملف
            $table->string('image')->nullable();  // صورة
            $table->string('receipt')->nullable();  // ايصال الدفع
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
