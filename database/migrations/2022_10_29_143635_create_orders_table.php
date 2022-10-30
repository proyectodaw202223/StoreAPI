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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->bigInteger('customerId')->unsigned();
            $table->decimal('amount', 8, 2);
            $table->dateTime('paymentDateTime');
            $table->longText('comments')->nullable();
            $table->enum('status', [
                'Created',
                'Paid',
                'In Management',
                'Sent',
                'Canceled'
            ]);

            $table->foreign('customerId')->references('id')->on('customers');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orders');
    }
};
