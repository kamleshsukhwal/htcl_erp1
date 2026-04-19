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
       Schema::table('boq_item_files', function (Blueprint $table) {

    $table->enum('approval_status', ['pending', 'approved', 'rejected'])
          ->default('pending')
          ->after('document_type');

    $table->unsignedBigInteger('approved_by')->nullable()->after('approval_status');

    $table->timestamp('approved_at')->nullable()->after('approved_by');

    $table->text('approval_remark')->nullable()->after('approved_at');
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('boq_item_files', function (Blueprint $table) {
            //
        });
    }
};
