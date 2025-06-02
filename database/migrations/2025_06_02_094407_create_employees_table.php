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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained(); // لحساب الموظف
            $table->foreignId('role_id')->constrained(); // Many to One عندي رول اسمو موظف بالقسم كذا و هاد الرول بينعطى لأكتر من موظف 
            $table->foreignId('manager_id')->constrained('managers'); // Many to One
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
