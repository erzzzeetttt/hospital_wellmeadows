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
        Schema::create('staff', function (Blueprint $table) {
    $table->id('staff_no');

    $table->unsignedBigInteger('role_id');
    $table->foreign('role_id')->references('role_id')->on('roles');

    $table->string('first_name');
    $table->string('last_name');
    $table->date('dob');
    $table->string('gender');
    $table->string('address');
    $table->string('phone_no');
    $table->string('position');
    $table->decimal('salary', 10, 2);

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff');
    }
};
