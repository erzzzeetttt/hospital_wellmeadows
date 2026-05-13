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
        Schema::create('diagnoses', function (Blueprint $table) {
    $table->id('diagnosis_id');

    $table->string('patient_no', 10);

$table->foreign('patient_no')
      ->references('patient_no')
      ->on('patients')
      ->cascadeOnDelete();

    $table->unsignedBigInteger('staff_no')->nullable();
    $table->foreign('staff_no')->references('staff_no')->on('staff')->nullOnDelete();

    $table->text('diagnosis_details');
    $table->date('diagnosis_date');

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('diagnoses');
    }
};
