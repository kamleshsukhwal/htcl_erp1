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
    Schema::table('boq_item_files', function (Blueprint $table) {
        $table->string('document_type')->nullable()->after('file_type');
    });
}

public function down()
{
    Schema::table('boq_item_files', function (Blueprint $table) {
        $table->dropColumn('document_type');
    });
}
};
