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
     Schema::create('invoices', function (Blueprint $table) {
    $table->id();
    $table->string('invoice_no')->unique();

    $table->unsignedBigInteger('customer_id')->nullable();
    $table->unsignedBigInteger('dc_out_id')->nullable();

    $table->date('invoice_date');

    $table->decimal('subtotal', 12, 2)->default(0);
    $table->decimal('tax', 12, 2)->default(0);
    $table->decimal('total', 12, 2)->default(0);

    $table->decimal('paid_amount', 12, 2)->default(0);

    $table->enum('status',['pending','partial','paid','cancelled'])->default('pending');

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
