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
        Schema::create('trabajos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("proyecto_id");
            $table->unsignedBigInteger("cliente_id");
            $table->decimal("costo", 24, 2);
            $table->unsignedBigInteger("moneda_id");
            $table->unsignedBigInteger("tipo_cambio_id");
            $table->decimal("cancelado", 24, 2);
            $table->decimal("saldo", 24, 2);
            $table->decimal("cancelado_bs", 24, 2);
            $table->decimal("saldo_bs", 24, 2);
            $table->decimal("costo_bs", 24, 2);
            $table->enum("estado_pago", ["PENDIENTE", "COMPLETO"]);
            $table->text("descripcion");
            $table->date("fecha_inicio");
            $table->integer("dias_plazo");
            $table->date("fecha_entrega");
            $table->enum("estado_trabajo", ["EN PROCESO", "ENVIADO", "CONCLUIDO"]);
            $table->date("fecha_envio")->nullable();
            $table->date("fecha_registro");
            $table->timestamps();

            $table->foreign("proyecto_id")->on("proyectos")->references("id");
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
        Schema::dropIfExists('trabajos');
    }
};
