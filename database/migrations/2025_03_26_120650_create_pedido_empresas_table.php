<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pedido_empresas', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('pedido_id');
            $table->unsignedBigInteger('empresa_id');

            $table->enum('estado_envio', ['pendiente', 'preparando', 'enviado', 'entregado', 'cancelado'])->default('pendiente');
            $table->timestamp('fecha_envio')->nullable();
            $table->decimal('precio_total', 8, 2)->default(0);

            $table->timestamps();

            $table->foreign('pedido_id')->references('id')->on('pedidos')->onDelete('cascade');
            $table->foreign('empresa_id')->references('id')->on('empresas')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pedido_empresas');
    }
};
