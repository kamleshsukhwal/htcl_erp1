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
    Schema::table('boqs', function (Blueprint $table) {
        $table->decimal('total_amount', 15, 2)->default(0)->after('status');
    });
}

public function down()
{
    Schema::table('boqs', function (Blueprint $table) {
        $table->dropColumn('total_amount');
    });
}

};
