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
        Schema::create('product_items', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->bigInteger('productId')->unsigned();
            $table->string('color', 7);
            $table->integer('stock');
            $table->enum('size', [
                'S',
                'M',
                'L',
                'ONE_SIZE'
            ]);

            $table->foreign('productId')->references('id')->on('products');

            $table->unique(['productId', 'color', 'size'], 'UX_productItems_prodId_color_size');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_items');
    }
};
