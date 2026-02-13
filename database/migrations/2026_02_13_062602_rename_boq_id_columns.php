<?php 
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->renameColumn('boq_id', 'boq_item_id');
        });

        Schema::table('dc_in_items', function (Blueprint $table) {
            $table->renameColumn('boq_id', 'boq_item_id');
        });
    }

    public function down(): void
    {
        Schema::table('purchase_order_items', function (Blueprint $table) {
            $table->renameColumn('boq_item_id', 'boq_id');
        });

        Schema::table('dc_in_items', function (Blueprint $table) {
            $table->renameColumn('boq_item_id', 'boq_id');
        });
    }
};
