<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Parametro extends Model
{
    use HasFactory;

    protected $fillable = [
        'nome_empresa',
        'endereco_completo',
        'telefone',
        'email',
        'cpf_cnpj',
        'redes_sociais',
        'logo',
        'versao_sistema',
        'data_atualizacao',
        'detalhe_atualizacao',
        'ativar_notificacao_usuario',
        'notificar_usuario',
        'ativar_notificacoes',
    ];

    protected $casts = [
        'ativar_notificacoes' => 'boolean',
    ];
}