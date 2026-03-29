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
        Schema::create('leave_balance',function(Blueprint $table)
        {
            $table->id();
            
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');

            $table->foreignId('leave_type_id')->constrained('leave_type')->onDelete('cascade');
            $table->integer('max_allowed');
            $table->integer('used_leave');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_balance');
    }
};
