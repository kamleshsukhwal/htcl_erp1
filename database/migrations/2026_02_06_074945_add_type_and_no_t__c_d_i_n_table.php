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
        Schema::table('dc_ins', function (Blueprint $table) {
             $table->string('dc_type')->nullable();
             //$table->string('dc_no')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dc_ins', function (Blueprint $table) {
             $table->dropColumn('cd_type');
           //  $table->dropColumn('cd_no');
        });
    }
};
