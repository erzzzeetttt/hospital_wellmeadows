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
        Schema::create('ward_requisition_items', function (Blueprint $table) {
    $table->id('requisition_item_id');

    $table->unsignedBigInteger('requisition_id');
    $table->foreign('requisition_id')
          ->references('requisition_id')
          ->on('ward_requisitions')
          ->cascadeOnDelete();

    $table->unsignedBigInteger('drug_id');
    $table->foreign('drug_id')->references('drug_id')->on('drugs');

    $table->integer('quantity_requested');
    $table->integer('quantity_supplied')->default(0);

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ward_requisition_items');
    }
};
