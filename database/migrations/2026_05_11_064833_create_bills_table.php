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
        Schema::create('bills', function (Blueprint $table) {
    $table->id('bill_id');

   $table->string('patient_no', 10);

    $table->foreign('patient_no')
      ->references('patient_no')
      ->on('patients')
      ->onDelete('cascade');

    $table->date('bill_date');
    $table->decimal('total_amount', 10, 2)->default(0);
    $table->string('status')->default('Unpaid');

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bills');
    }
};
