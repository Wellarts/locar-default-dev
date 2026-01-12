<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class FluxoCaixa extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'valor',
        'locacao_id',
        'contas_receber_id',
        'contas_pagar_id',
        'despesa_id',
        'tipo',
        'obs',
       
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
        ->logOnly(['*']);
        // Chain fluent methods for configuration options
    }
}
