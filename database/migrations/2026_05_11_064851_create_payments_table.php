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
        Schema::create('payments', function (Blueprint $table) {
    $table->id('payment_id');

    $table->unsignedBigInteger('bill_id');
    $table->foreign('bill_id')->references('bill_id')->on('bills')->cascadeOnDelete();

    $table->date('payment_date');
    $table->decimal('amount_paid', 10, 2);
    $table->string('payment_method');
    $table->string('payment_status')->default('Completed');

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
