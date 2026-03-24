<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->tinyInteger('status')->default(0)->after('guard_name');
            $table->unsignedBigInteger('created_by')->default(0)->after('status');
            $table->unsignedBigInteger('updated_by')->default(0)->after('created_by');
        });

        Schema::table('modules', function (Blueprint $table) {
            $table->tinyInteger('status')->default(0)->after('is_enabled');
            $table->unsignedBigInteger('created_by')->default(0)->after('status');
            $table->unsignedBigInteger('updated_by')->default(0)->after('created_by');
        });
    }

    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn(['status', 'created_by', 'updated_by']);
        });

        Schema::table('modules', function (Blueprint $table) {
            $table->dropColumn(['status', 'created_by', 'updated_by']);
        });
    }
};
