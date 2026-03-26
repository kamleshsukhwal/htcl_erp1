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
        Schema::table('leave_type',function(Blueprint $table){
            $table->renameColumn('carr_forward_enabled','credit_forward_enabled');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
         Schema::table('leave_type',function(Blueprint $table){
            $table->renameColumn('credit_forward_enabled','carr_forward_enabled');
        });
    }
};
