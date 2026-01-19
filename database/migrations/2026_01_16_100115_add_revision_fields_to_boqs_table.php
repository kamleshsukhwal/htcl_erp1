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
        Schema::table('boqs', function (Blueprint $table) {
        $table->integer('revision_no')->default(0)->after('project_id');
        $table->unsignedBigInteger('parent_boq_id')->nullable()->after('revision_no');
        $table->boolean('is_locked')->default(false);
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('boqs', function (Blueprint $table) {
            //
        });
    }
};
