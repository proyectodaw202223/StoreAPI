<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('seasonal_sale_lines', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->bigInteger('seasonalSaleId')->unsigned();
            $table->bigInteger('itemId')->unsigned();
            $table->decimal('discountPercentage', 5, 2);

            $table->foreign('seasonalSaleId')->references('id')->on('seasonal_sales');
            $table->foreign('itemId')->references('id')->on('product_items');

            $table->unique(['seasonalSaleId', 'itemId'], 'UX_seasonalSaleLines_seasonalSaleId_itemId');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('seasonal_sale_lines');
    }
};
