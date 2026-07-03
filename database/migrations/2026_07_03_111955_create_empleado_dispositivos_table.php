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
        Schema::create('empleado_dispositivos', function (Blueprint $table) {

            $table->id();

            $table->foreignId('empresa_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('empleado_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('tipo', 20)  
                ->default('telegram');  // telegram, whatsapp, app

            $table->string('identificador')->unique(); // chat_id, token, etc

            $table->string('usuario')->nullable(); // @username de Telegram

            $table->string('celular', 20)->nullable();

            $table->boolean('activo')
                ->default(true);

            $table->timestamps();

             $table->unique([
                'empresa_id',
                'empleado_id',
                'tipo'
            ]);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('empleado_dispositivos');
    }
};
