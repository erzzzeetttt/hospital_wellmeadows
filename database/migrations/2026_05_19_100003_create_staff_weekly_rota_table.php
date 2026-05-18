<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_weekly_rota', function (Blueprint $table) {
            $table->id('rota_id');

            $table->unsignedBigInteger('staff_no');
            $table->foreign('staff_no')->references('staff_no')->on('staff')->cascadeOnDelete();

            $table->unsignedBigInteger('ward_id');
            $table->foreign('ward_id')->references('ward_id')->on('wards');

            $table->date('week_start_date');
            $table->string('shift_type', 20); // Early, Late, Night

            $table->timestamps();

            $table->unique(['staff_no', 'ward_id', 'week_start_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_weekly_rota');
    }
};
