<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Contrato extends Model
{
    use HasFactory;

    protected $fillable = ['titulo', 'descricao'];

    protected $casts = [
        'descricao' => 'array',
    ];

    // Acessor para exibir imagens corretamente
    public function getDescricaoFormatadaAttribute()
    {
        if (!$this->descricao) {
            return null;
        }

        // Substituir caminhos relativos por URLs completas
        $descricao = str_replace(
            'src="/storage/',
            'src="' . asset('storage') . '/',
            $this->descricao
        );

        return $descricao;
    }
}