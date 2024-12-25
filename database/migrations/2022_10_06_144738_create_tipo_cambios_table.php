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
        Schema::create('tipo_cambios', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("moneda1_id");
            $table->decimal("valor1", 24, 2);
            $table->unsignedBigInteger("moneda2_id");
            $table->decimal("valor2", 24, 2);
            $table->unsignedBigInteger("menor_valor");
            $table->integer("defecto");
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
        Schema::dropIfExists('tipo_cambios');
    }
};
