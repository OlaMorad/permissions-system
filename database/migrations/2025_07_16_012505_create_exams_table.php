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
        Schema::create('exams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('specialization_id')->constrained();
            $table->foreignId('program_id')->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            $table->string('day');
            $table->date('date');
            $table->string('status')->nullable()->default(null);
            $table->float('simple_ratio');
            $table->float('average_ratio');
            $table->float('hard_ratio');
            $table->unsignedInteger('candidates_count')->nullable();
            $table->unsignedInteger('present_candidates_count')->nullable();
            $table->decimal('success_rate', 5, 2)->nullable();
            $table->time('start_time');
            $table->time('end_time');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exams');
    }
};
