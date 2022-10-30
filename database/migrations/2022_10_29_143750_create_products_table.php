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
            $table->string('name');
            $table->longText('description')->nullable();
            $table->decimal('price', 8, 2);
            $table->enum('category', [
                'Bisutería',
                'Lana',
                'Hama - Beads',
                'Personalizaciones'
            ]);
            $table->enum('subcategory', [
                'Colgantes',
                'Pendientes',
                'Pulseras',
                'Patucos',
                'Gorros',
                'Posavasos',
                'Marcos de Fotos',
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
