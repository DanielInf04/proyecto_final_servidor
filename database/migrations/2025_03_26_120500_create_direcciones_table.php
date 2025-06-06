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
        Schema::create('direcciones', function (Blueprint $table) {
            $table->id();
            $table->string('calle');
            $table->string('puerta')->nullable();
            $table->string('piso')->nullable();
            $table->string('pais');
            //$table->string('ciudad');
            //$table->string('provincia');
            $table->string('codigo_postal');

            $table->unsignedBigInteger('poblacion_id');

            // Relación para que los clientes puedan guardar direcciones
            $table->unsignedBigInteger('cliente_id')->nullable();

            $table->foreign('poblacion_id')->references('id')->on('poblaciones')->onDelete('cascade');
            $table->foreign('cliente_id')->references('id')->on('clientes')->onDelete('set null');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('direcciones_pedido');
    }
};
