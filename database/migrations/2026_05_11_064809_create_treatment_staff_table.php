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
        Schema::create('treatment_staff', function (Blueprint $table) {
    $table->id();

    $table->unsignedBigInteger('treatment_id');
    $table->foreign('treatment_id')
          ->references('treatment_id')
          ->on('treatments');

    $table->unsignedBigInteger('staff_no');
    $table->foreign('staff_no')
          ->references('staff_no')
          ->on('staff');

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('treatment_staff');
    }
};
