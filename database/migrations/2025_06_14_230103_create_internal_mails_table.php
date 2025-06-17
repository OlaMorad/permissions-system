<?php

use App\Enums\StatusInternalMail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('internal_mails', function (Blueprint $table) {
            $table->id();
            $table->foreignId('from_user_id')->constrained('users')->cascadeOnDelete()->cascadeOnUpdate();
            // $table->foreignId('to_user_id')->constrained('users')->cascadeOnDelete()->cascadeOnUpdate();
            $table->string('status')->default(StatusInternalMail::PENDING->value);
            $table->String('subject');
            $table->text('body');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('internal_mails');
    }
};
