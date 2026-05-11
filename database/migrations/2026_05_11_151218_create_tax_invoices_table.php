<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tax_invoices', function (Blueprint $table) {

            $table->id();

            $table->foreignId('purchase_order_id')
                  ->constrained('purchase_orders')
                  ->onDelete('cascade');

            $table->string('invoice_no')->unique();

            $table->date('invoice_date');

            $table->decimal('invoice_amount', 15, 2)->default(0);

            $table->decimal('gst_amount', 15, 2)->default(0);

            $table->decimal('tds_amount', 15, 2)->default(0);

            $table->decimal('payable_amount', 15, 2)->default(0);

            $table->text('remarks')->nullable();

            $table->string('invoice_file')->nullable();

            $table->enum('status', [
                'uploaded',
                'verified',
                'paid'
            ])->default('uploaded');

            $table->unsignedBigInteger('uploaded_by')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tax_invoices');
    }
};