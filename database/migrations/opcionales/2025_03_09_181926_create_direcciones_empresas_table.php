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
        Schema::create('direcciones_empresas', function (Blueprint $table) {
            $table->id();
            $table->string('calle');
            $table->string('codigo_postal');
            $table->string('ciudad');
            $table->string('provincia');
            $table->string('pais');

            // Relación 1:1 con empresas
            $table->unsignedBigInteger('empresa_id')->unique();
            $table->foreign('empresa_id')->references('id')->on('empresas')->onDelete('cascade');

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
        Schema::dropIfExists('direcciones_empresas');
    }
};
