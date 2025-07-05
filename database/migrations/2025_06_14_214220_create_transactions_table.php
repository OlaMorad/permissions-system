<?php

use App\Enums\TransactionStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\StatusInternalMail;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('form_content_id')->references('id')->on('form_contents');
            $table->foreignId('from')->nullable()->references('id')->on('paths');
            $table->foreignId('to')->nullable()->references('id')->on('paths');
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->string('status_from')->default(TransactionStatus::PENDING->value);
            $table->string('status_to')->nullable()->default(TransactionStatus::PENDING->value);
            $table->string('receipt_number',6)->unique();
            $table->string('receipt_status')->default(StatusInternalMail::PENDING->value);
            $table->foreignId('changed_by')->nullable()->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
