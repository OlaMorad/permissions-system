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
      Schema::create('form_element_values', function (Blueprint $table) {
    $table->id();
    $table->foreignId('form_content_id')->references('id')->on('form_contents')->onDelete('cascade');
    $table->foreignId('form_element_id')->references('id')->on('form_elements')->onDelete('cascade');
    $table->text('value')->nullable();  // القيمة المدخلة
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('form_element_values');
    }
};
