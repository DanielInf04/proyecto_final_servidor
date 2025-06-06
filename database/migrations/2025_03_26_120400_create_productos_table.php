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
        Schema::create('productos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->text('descripcion')->nullable();

            $table->decimal('precio_base', 10, 2); // Precio sin IVA

            // Campos para ofertas
            $table->decimal('precio_oferta', 10, 2)->nullable();
            $table->integer('descuento_porcentaje')->nullable();
            $table->boolean('oferta_activa')->default(false);

            $table->integer('stock')->default(0);
            $table->enum('estado', ['activo', 'inactivo'])->default('activo');
            //$table->string('imagen');

            // Clave foraneas
            $table->unsignedBigInteger('categoria_id');
            $table->unsignedBigInteger('empresa_id');

            $table->timestamps();

            $table->foreign('categoria_id')->references('id')->on('categorias')->onDelete('cascade');
            $table->foreign('empresa_id')->references('id')->on('empresas')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('productos');
    }
};
