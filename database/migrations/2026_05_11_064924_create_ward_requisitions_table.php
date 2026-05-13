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
        Schema::create('ward_requisitions', function (Blueprint $table) {
    $table->id('requisition_id');

    $table->unsignedBigInteger('ward_id');
    $table->foreign('ward_id')->references('ward_id')->on('wards');

    $table->unsignedBigInteger('staff_no');
    $table->foreign('staff_no')->references('staff_no')->on('staff');

    $table->date('requisition_date');
    $table->string('status')->default('Pending');

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ward_requisitions');
    }
};
