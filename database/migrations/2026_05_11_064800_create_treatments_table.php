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
        Schema::create('treatments', function (Blueprint $table) {
    $table->id('treatment_id');

    $table->string('patient_no', 10);
    $table->foreign('patient_no')
      ->references('patient_no')
      ->on('patients')
      ->onDelete('cascade');

    $table->text('treatment_details');
    $table->date('treatment_date');

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('treatments');
    }
};
