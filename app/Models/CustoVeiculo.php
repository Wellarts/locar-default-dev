<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class CustoVeiculo extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [

        'fornecedor_id',
        'veiculo_id',
        'km_atual',
        'data',
        'descricao',
        'valor',
        'financeiro',
        'pago',
        'parcelas',
        'categoria_id',


    ];

    public function Veiculo()
    {
        return $this->belongsTo(Veiculo::class);
    }

    public function Fornecedor()
    {
        return $this->belongsTo(Fornecedor::class);
    }

    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['*']);
        // Chain fluent methods for configuration options
    }
}
