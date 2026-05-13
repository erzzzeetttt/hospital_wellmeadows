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
       Schema::create('ward_allocations', function (Blueprint $table) {
    $table->id('allocation_id');

   $table->string('patient_no', 10);

$table->foreign('patient_no')
      ->references('patient_no')
      ->on('patients')
      ->cascadeOnDelete();

    $table->unsignedBigInteger('ward_id');
    $table->foreign('ward_id')->references('ward_id')->on('wards');

    $table->unsignedBigInteger('bed_id');
    $table->foreign('bed_id')->references('bed_id')->on('beds');

    $table->date('allocation_date');
    $table->date('release_date')->nullable();

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ward_allocations');
    }
};
