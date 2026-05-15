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
        Schema::create('appointments', function (Blueprint $table) {
    $table->id('appointment_id');

    $table->string('patient_no', 10);
    $table->foreign('patient_no')
          ->references('patient_no')
          ->on('patients')
          ->onDelete('cascade');

    $table->date('appointment_date');
    $table->time('appointment_time');
    $table->string('status')->default('Pending');

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
