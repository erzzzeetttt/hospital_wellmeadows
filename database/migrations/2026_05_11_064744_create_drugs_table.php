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
        Schema::create('drugs', function (Blueprint $table) {
    $table->id('drug_id');

    $table->unsignedBigInteger('supplierno');
    $table->foreign('supplierno')
          ->references('supplierno')
          ->on('suppliers');

    $table->string('drug_name');
    $table->integer('quantity_stock');
    $table->decimal('unit_cost', 10, 2);

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('drugs');
    }
};
