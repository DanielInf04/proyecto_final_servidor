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
        Schema::create('direcciones_pedidos', function (Blueprint $table) {
            $table->id();
            $table->string('calle');
            $table->string('codigo_postal');
            $table->string('ciudad');
            $table->string('provincia');
            $table->string('pais');

            // Relación 1:1 con pedidos
            $table->unsignedBigInteger('pedido_id')->unique();
            $table->foreign('pedido_id')->references('id')->on('pedidos')->onDelete('cascade');

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
        Schema::dropIfExists('direcciones_pedidos');
    }
};
