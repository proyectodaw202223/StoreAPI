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
        Schema::create('order_lines', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->bigInteger('orderId')->unsigned();
            $table->bigInteger('itemId')->unsigned();
            $table->integer('quantity');
            $table->decimal('priceWithDiscount', 8, 2);
            $table->decimal('amount', 8, 2);

            $table->foreign('orderId')->references('id')->on('orders');
            $table->foreign('itemId')->references('id')->on('product_items');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('order_lines');
    }
};
