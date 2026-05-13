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
        Schema::create('patients', function (Blueprint $table) {
    $table->id('patient_no');

    $table->unsignedBigInteger('doctor_id');
    $table->foreign('doctor_id')
          ->references('doctor_id')
          ->on('local_doctors');

    $table->unsignedBigInteger('nextofkinid');
    $table->foreign('nextofkinid')
          ->references('nextofkinid')
          ->on('next_of_kin');

    $table->string('first_name');
    $table->string('last_name');
    $table->date('date_of_birth');
    $table->string('gender');
    $table->string('address');
    $table->string('phone_no');
    $table->string('marital_status')->nullable();

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};
