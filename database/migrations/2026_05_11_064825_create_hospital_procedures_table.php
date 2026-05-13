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
        Schema::create('hospital_procedures', function (Blueprint $table) {
    $table->id('procedure_id');

    $table->unsignedBigInteger('patient_no');
    $table->foreign('patient_no')->references('patient_no')->on('patients');

    $table->string('procedure_name');
    $table->text('description')->nullable();
    $table->date('procedure_date');
    $table->decimal('cost', 10, 2);

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hospital_procedures');
    }
};
