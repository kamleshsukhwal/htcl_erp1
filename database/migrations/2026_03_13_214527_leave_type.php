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
        Schema::create('leave_type',function(Blueprint $table){
            $table->id();
            $table->string('name');
            $table->integer('max_allowed_days');
            $table->boolean('accural_enabled')->default(true);
            $table->float('accrual_rate')->nullable();
            $table->boolean('credit_forward_enabled')->default(false);
            $table->boolean('is_paid')->default(true);
            $table->boolean('half_day_allowed')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_type');
    }
};
