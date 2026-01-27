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
    Schema::create('purchase_orders', function (Blueprint $table) {
        $table->id();
        $table->string('po_number')->unique();
        $table->foreignId('vendor_id')->constrained();
        $table->foreignId('project_id')->constrained();
        $table->date('order_date'); // <-- Added order_date column
        $table->enum('status', ['pending', 'approved', 'closed'])->default('pending');
        $table->timestamps();
    });


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};
