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
        Schema::create('pedidos', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('cliente_id');
            $table->unsignedBigInteger('direccion_id')->nullable();
            $table->unsignedBigInteger('contacto_entrega_id')->nullable();

            $table->decimal('total', 8, 2)->default(0);
            $table->enum('status', ['pendiente', 'pagado', 'cancelado'])->default('pendiente');
            $table->timestamp('fecha_pedido')->useCurrent();

            $table->timestamps();

            $table->foreign('cliente_id')->references('id')->on('clientes')->onDelete('cascade');
            $table->foreign('direccion_id')->references('id')->on('direcciones')->onDelete('set null');
            $table->foreign('contacto_entrega_id')->references('id')->on('contacto_entregas')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pedidos');
    }
};
