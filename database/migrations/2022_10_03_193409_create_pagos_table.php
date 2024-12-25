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
            $table->unsignedBigInteger("trabajo_id");
            $table->unsignedBigInteger("cliente_id");
            $table->decimal("monto", 24, 2);
            $table->unsignedBigInteger("moneda_id");
            $table->unsignedBigInteger("tipo_cambio_id");
            $table->decimal("monto_bs", 24, 2);
            $table->text("descripcion");
            $table->date("fecha_pago");
            $table->timestamps();
            $table->foreign("trabajo_id")->on("trabajos")->references("id");
            $table->foreign("cliente_id")->on("clientes")->references("id");
            $table->foreign("moneda_id")->on("monedas")->references("id");
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
