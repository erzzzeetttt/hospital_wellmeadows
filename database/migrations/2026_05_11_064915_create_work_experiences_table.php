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
       Schema::create('work_experiences', function (Blueprint $table) {
    $table->id('experience_id');

    $table->unsignedBigInteger('staff_no');
    $table->foreign('staff_no')->references('staff_no')->on('staff')->cascadeOnDelete();

    $table->string('organization');
    $table->string('position');
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
        Schema::dropIfExists('work_experiences');
    }
};
