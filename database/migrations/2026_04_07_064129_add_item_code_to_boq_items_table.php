<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('boq_items', function (Blueprint $table) {
            $table->string('item_code', 100)->nullable()->after('id');
        });
    }

    public function down()
    {
        Schema::table('boq_items', function (Blueprint $table) {
            $table->dropColumn('item_code');
        });
    }
};