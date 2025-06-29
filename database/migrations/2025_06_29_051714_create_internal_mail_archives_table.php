<?php

use App\Enums\StatusInternalMail;
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
        Schema::create('internal_mail_archives', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->index();
            $table->string('subject');
            $table->enum('status', array_column(StatusInternalMail::cases(), 'value'));
            $table->json('to');          // أسماء الدوائر بصيغة JSON
            $table->json('to_phones');   // أرقام هواتف المستلمين بصيغة JSON
            $table->timestamp('received_at')->nullable(); // في حال المدير
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('internal_mail_archives');
    }
};
