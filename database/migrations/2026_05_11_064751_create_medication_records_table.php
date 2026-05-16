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
        Schema::create('medication_records', function (Blueprint $table) {

    $table->id('medication_id');

    $table->string('patient_no', 10);
    $table->foreign('patient_no')
          ->references('patient_no')
          ->on('patients')
          ->onDelete('cascade');

    $table->unsignedBigInteger('drug_id');
    $table->foreign('drug_id')
          ->references('drug_id')
          ->on('drugs')
          ->onDelete('cascade');

    $table->string('dosage');
    $table->string('frequency');

    $table->date('start_date');

    $table->date('end_date')->nullable();

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medication_records');
    }
};
