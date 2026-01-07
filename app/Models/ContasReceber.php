<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ContasReceber extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'cliente_id',
        'locacao_id',        
        'parcelas',
        'ordem_parcela',
        'forma_pgmto_id',
        'data_vencimento',
        'data_recebimento',
        'status',
        'valor_total',
        'valor_parcela',
        'valor_recebido',
        'obs',
        'categoria_id',
    ];

    // Adicione casts para melhorar performance
    protected $casts = [
        'data_vencimento' => 'date',
        'data_recebimento' => 'date',
        'status' => 'boolean',
        'valor_total' => 'decimal:2',
        'valor_parcela' => 'decimal:2',
        'valor_recebido' => 'decimal:2',
    ];

    // Use eager loading por padrão para relações frequentemente usadas
    protected $with = ['cliente', 'categoria'];

    // Nome correto (minúsculo) para seguir convenções Laravel
    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    public function formaPgmto()
    {
        return $this->belongsTo(FormaPagamento::class);
    }

    public function locacao()
    {
        return $this->belongsTo(Locacao::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['*'])
            ->logOnlyDirty() // Só loga campos que mudaram
            ->dontSubmitEmptyLogs();
    }

    // Adicione escopos para queries comuns
    public function scopeRecebidas($query)
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