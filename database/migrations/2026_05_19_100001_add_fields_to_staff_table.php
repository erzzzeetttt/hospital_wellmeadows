<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('staff', function (Blueprint $table) {
            $table->string('nin', 20)->nullable()->after('phone_no');
            $table->string('salary_scale', 20)->nullable()->after('salary');
            $table->decimal('hours_per_week', 5, 2)->nullable()->after('salary_scale');
            $table->string('contract_type', 20)->nullable()->after('hours_per_week');
            $table->string('payment_type', 20)->nullable()->after('contract_type');
        });
    }

    public function down(): void
    {
        Schema::table('staff', function (Blueprint $table) {
            $table->dropColumn(['nin', 'salary_scale', 'hours_per_week', 'contract_type', 'payment_type']);
        });
    }
};
