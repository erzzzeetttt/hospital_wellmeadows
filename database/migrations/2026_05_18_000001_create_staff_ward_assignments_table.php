<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_ward_assignments', function (Blueprint $table) {
            $table->id('assignment_id');

            $table->unsignedBigInteger('staff_no');
            $table->foreign('staff_no')->references('staff_no')->on('staff')->cascadeOnDelete();

            $table->unsignedBigInteger('ward_id');
            $table->foreign('ward_id')->references('ward_id')->on('wards');

            $table->date('assignment_date');
            $table->date('end_date')->nullable();
            $table->string('role_in_ward')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_ward_assignments');
    }
};
