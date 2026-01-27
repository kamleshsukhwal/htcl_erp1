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
       Schema::create('dc_ins', function (Blueprint $table) {
    $table->id();
    $table->string('dc_number')->unique();
    $table->foreignId('vendor_id')->constrained();
    $table->foreignId('purchase_order_id')->constrained();
    $table->enum('delivery_channel',['vendor','warehouse','site']);
    $table->date('delivery_date');
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dc_ins');
    }
};
