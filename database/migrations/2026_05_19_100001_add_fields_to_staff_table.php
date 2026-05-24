<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('staff', function (Blueprint $table) {
            if (!Schema::hasColumn('staff', 'nin')) {
                $table->string('nin', 20)->nullable()->after('phone_no');
            }
            if (!Schema::hasColumn('staff', 'salary_scale')) {
                $table->string('salary_scale', 20)->nullable()->after('salary');
            }
            if (!Schema::hasColumn('staff', 'hours_per_week')) {
                $table->decimal('hours_per_week', 5, 2)->nullable()->after('salary_scale');
            }
            if (!Schema::hasColumn('staff', 'contract_type')) {
                $table->string('contract_type', 20)->nullable()->after('hours_per_week');
            }
            if (!Schema::hasColumn('staff', 'payment_type')) {
                $table->string('payment_type', 20)->nullable()->after('contract_type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('staff', function (Blueprint $table) {
            if (Schema::hasColumn('staff', 'nin')) {
                $table->dropColumn('nin');
            }
            if (Schema::hasColumn('staff', 'salary_scale')) {
                $table->dropColumn('salary_scale');
            }
            if (Schema::hasColumn('staff', 'hours_per_week')) {
                $table->dropColumn('hours_per_week');
            }
            if (Schema::hasColumn('staff', 'contract_type')) {
                $table->dropColumn('contract_type');
            }
            if (Schema::hasColumn('staff', 'payment_type')) {
                $table->dropColumn('payment_type');
            }
        });
    }
};
