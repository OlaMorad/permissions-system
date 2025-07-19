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
        Schema::create('candidates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained('exams')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('doctor_id')->constrained('doctors')->cascadeOnDelete();
            $table->string('status')->nullable();
            $table->decimal('degree', 5, 2)->nullable()->check('degree >= 0 AND degree <= 100');
            $table->string('rating')->nullable();
            $table->integer('exam_number')->unique();
            $table->date('exam_date');
            $table->date('nomination_date')->nullable(); // تاريخ الترشيح
            // تأكد من عدم تكرار نفس الدكتور على نفس الامتحان
            $table->unique(['doctor_id', 'exam_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('candidates');
    }
};
