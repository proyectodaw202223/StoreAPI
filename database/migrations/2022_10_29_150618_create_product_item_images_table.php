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
        Schema::create('product_item_images', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->bigInteger('itemId')->unsigned();
            $table->string('imagePath', 255);
            $table->string('url', 255);

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
        Schema::dropIfExists('product_item_images');
    }
};
