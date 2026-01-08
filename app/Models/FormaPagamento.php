<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormaPagamento extends Model
{
    use HasFactory;

    protected $fillable = ['nome'];

    public function ordemServico()
    {
        return $this->hasMany(OrdemServico::class);
    }

    public function contasReceber()
    {
        return $this->hasMany(ContasReceber::class);
    }

    public function contasPagar()
    {
        return $this->hasMany(ContasPagar::class);
    }

    public function locacao()
    {
        return $this->hasMany(Locacao::class);
    }
}
