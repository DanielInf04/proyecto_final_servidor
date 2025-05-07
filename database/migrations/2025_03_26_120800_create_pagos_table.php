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
        Schema::create('pagos', function (Blueprint $table) {
            $table->id();
            //$table->decimal('monto', 8, 2);
            $table->enum('estado', ['pendiente', 'pagado', 'fallido'])->default('pendiente');
            $table->string('metodo_pago'); // tarjeta, Paypal...
            $table->dateTime('fecha_pago')->nullable();

            // Referencia al ID de la transacción
            $table->string('referencia')->nullable();

            // Relación 1:1 con pedido
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
        Schema::dropIfExists('pagos');
    }
};
