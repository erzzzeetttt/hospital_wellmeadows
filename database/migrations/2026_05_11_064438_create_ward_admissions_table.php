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
        Schema::create('ward_admissions', function (Blueprint $table) {
    $table->id('admission_id');

    $table->string('patient_no', 10);
    $table->foreign('patient_no')
          ->references('patient_no')
          ->on('patients')
          ->onDelete('cascade');

    $table->date('date_admitted');
    $table->date('expected_leave_date')->nullable();
    $table->string('status')->default('Admitted');

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ward_admissions');
    }
};
