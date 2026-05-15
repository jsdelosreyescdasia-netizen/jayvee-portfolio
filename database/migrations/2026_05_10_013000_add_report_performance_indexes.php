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
        Schema::table('daily_reports', function (Blueprint $table) {
            $table->index(['user_id', 'title'], 'daily_reports_user_title_index');
            $table->index(['user_id', 'prepared_by'], 'daily_reports_user_prepared_by_index');
        });

        Schema::table('report_issuances', function (Blueprint $table) {
            $table->index('issuance_no', 'report_issuances_issuance_no_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('report_issuances', function (Blueprint $table) {
            $table->dropIndex('report_issuances_issuance_no_index');
        });

        Schema::table('daily_reports', function (Blueprint $table) {
            $table->dropIndex('daily_reports_user_prepared_by_index');
            $table->dropIndex('daily_reports_user_title_index');
        });
    }
};
