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
        Schema::create('parametros', function (Blueprint $table) {
            $table->id();
            $table->string('nome_empresa')->nullable();
            $table->string('endereco_completo')->nullable();
            $table->string('telefone')->nullable();
            $table->string('email')->nullable();
            $table->string('cpf_cnpj')->nullable();
            $table->string('redes_sociais')->nullable();
            $table->string('logo')->nullable();
            $table->string('versao_sistema')->nullable();
            $table->string('data_atualizacao')->nullable();
            $table->string('detalhe_atualizacao')->nullable();
            $table->boolean('ativar_notificacoes')->default(true);
            $table->string('notificar_usuario')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parametros');
    }
};
