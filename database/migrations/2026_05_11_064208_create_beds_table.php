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
        Schema::create('beds', function (Blueprint $table) {
    $table->id('bed_id');

    $table->unsignedBigInteger('ward_id');
    $table->foreign('ward_id')->references('ward_id')->on('wards');

    $table->string('bed_number');
    $table->string('status')->default('Available');

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('beds');
    }
};
