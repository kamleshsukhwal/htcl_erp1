<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up()
{
    Schema::create('vendor_payments', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('po_id');
        $table->unsignedBigInteger('vendor_id');

        $table->decimal('amount', 12, 2);
        $table->date('payment_date');

        $table->string('mode');
        $table->string('txn_ref_no')->nullable();
        $table->string('attachment')->nullable();

        $table->timestamps();

        // Optional FK
        $table->foreign('po_id')->references('id')->on('purchase_orders')->onDelete('cascade');
        $table->foreign('vendor_id')->references('id')->on('vendors')->onDelete('cascade');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendor_payments');
    }
};
