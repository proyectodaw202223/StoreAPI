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
        Schema::create('seasonal_sales', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('slogan');
            $table->string('description', 255)->nullable();
            $table->dateTime('validFromDateTime');
            $table->dateTime('validToDateTime');
            $table->boolean('isCanceled');
            $table->dateTime('canceledAtDateTime')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('seasonal_sales');
    }
};
