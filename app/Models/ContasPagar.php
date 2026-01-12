<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ContasPagar extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'fornecedor_id',
        'parcelas',
        'despesa_id',
        'ordem_parcela',
        'forma_pgmto_id',
        'data_vencimento',
        'data_pagamento',
        'status',
        'valor_total',
        'valor_parcela',
        'valor_pago',
        'obs',
        'categoria_id',
    ];

    // Adicione casts para melhorar performance
    protected $casts = [
        'data_vencimento' => 'date',
        'data_pagamento' => 'date',
        'status' => 'boolean',
        'valor_total' => 'decimal:2',
        'valor_parcela' => 'decimal:2',
        'valor_pago' => 'decimal:2',
       
    ];

    // Use eager loading por padrão para relações frequentemente usadas
    protected $with = ['fornecedor', 'categoria'];

    public function fornecedor()
    {
        return $this->belongsTo(Fornecedor::class);
    }

    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    public function formaPgmto()
    {
        return $this->belongsTo(FormaPagamento::class);
    }

    public function fluxoCaixa()
    {
        return $this->hasOne(FluxoCaixa::class, 'contas_pagar_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['*'])
            ->logOnlyDirty() // Só loga campos que mudaram
            ->dontSubmitEmptyLogs();
    }

    // Adicione escopos para queries comuns
    public function scopePagas($query)
    {
        return $query->where('status', true);
    }

    public function scopePendentes($query)
    {
        return $query->where('status', false);
    }

    public function scopeVencidas($query)
    {
        return $query->where('data_vencimento', '<', now())
                    ->where('status', false);
    }

    
}