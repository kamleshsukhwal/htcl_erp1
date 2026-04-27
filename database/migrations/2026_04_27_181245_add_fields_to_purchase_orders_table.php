<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsToPurchaseOrdersTable extends Migration
{
    public function up()
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->decimal('gst_amount', 12, 2)->default(0)->after('total_amount');
            $table->text('t_c')->nullable()->after('gst_amount');
            $table->text('notes')->nullable()->after('t_c');
        });
    }

    public function down()
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropColumn(['gst_amount', 't_c', 'notes']);
        });
    }
}