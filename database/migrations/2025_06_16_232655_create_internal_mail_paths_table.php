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
        Schema::create('internal_mail_paths', function (Blueprint $table) {
            $table->id();

            $table->foreignId('internal_mail_id')
                ->constrained('internal_mails')
                ->cascadeOnDelete();

            $table->foreignId('path_id')
                ->constrained('paths')
                ->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('internal_mail_paths');
    }
};
