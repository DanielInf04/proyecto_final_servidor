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
        Schema::create('detalle_pedidos', function (Blueprint $table) {
            $table->id();
            
            $table->unsignedBigInteger('pedido_empresa_id');
            $table->unsignedBigInteger('producto_id');

            $table->integer('cantidad');
            $table->decimal('precio_unitario', 8, 2);

            $table->timestamps();

            $table->foreign('pedido_empresa_id')->references('id')->on('pedido_empresas')->onDelete('cascade');
            $table->foreign('producto_id')->references('id')->on('productos')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('detalle_pedidos');
    }
};
