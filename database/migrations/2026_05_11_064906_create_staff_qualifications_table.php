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
        Schema::create('staff_qualifications', function (Blueprint $table) {
    $table->id('qualification_id');

    $table->unsignedBigInteger('staff_no');
    $table->foreign('staff_no')->references('staff_no')->on('staff')->cascadeOnDelete();

    $table->string('qualification_type');
    $table->string('institution');
    $table->date('date_obtained');

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff_qualifications');
    }
};
