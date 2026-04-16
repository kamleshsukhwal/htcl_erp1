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
        Schema::table('invoices', function (Blueprint $table) {
            $table->decimal('cgst', 12, 2)->default(0);
        $table->decimal('sgst', 12, 2)->default(0);
        $table->decimal('igst', 12, 2)->default(0);
        $table->decimal('gst_percent', 5, 2)->default(18);

        $table->string('customer_gstin')->nullable();
        $table->string('company_gstin')->nullable();
        $table->string('place_of_supply')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            //
        });
    }
};
