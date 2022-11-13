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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('name', 60);
            $table->string('description', 255)->nullable();
            $table->decimal('price', 8, 2);
            $table->enum('category', [
                'Bisuteria',
                'Lana',
                'HamaBeads',
                'Personalizaciones'
            ]);
            $table->enum('subcategory', [
                'Colgantes',
                'Pendientes',
                'Pulseras',
                'Patucos',
                'Gorros',
                'Posavasos',
                'MarcosFotos',
                'Carteras',
                'Llaveros'
            ])->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('products');
    }
};
