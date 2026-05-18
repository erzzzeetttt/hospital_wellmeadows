<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wards', function (Blueprint $table) {
            $table->string('telephone_extension', 20)->nullable()->after('location');
        });
    }

    public function down(): void
    {
        Schema::table('wards', function (Blueprint $table) {
            $table->dropColumn('telephone_extension');
        });
    }
};
